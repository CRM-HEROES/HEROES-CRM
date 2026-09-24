#!/usr/bin/env python3
import hashlib
import os
import re
import socket
import time
import uuid

USER = os.environ.get("KAVKOM_EXTENSION", "501")
PASSWORD = os.environ.get("KAVKOM_PASSWORD", "")
REALM = os.environ.get("KAVKOM_USER_CONTEXT", "aria-madacom.kavkom.com")
DOMAIN = REALM
TARGET_HOST = "127.0.0.1"
TARGET_PORT = 15062
EXTERNAL_IP = os.environ.get("EXTERNAL_IP", "127.0.0.1")
EXTERNAL_SIP_PORT = os.environ.get("EXTERNAL_SIP_PORT", "5080")
EXPIRES = int(os.environ.get("KAVKOM_EXPIRES_SECONDS", "600"))
TRANSPORT = os.environ.get("KAVKOM_SIP_TRANSPORT", "tls").lower()
KAVKOM_PROXY_PORT = os.environ.get("KAVKOM_SIP_PORT", "5061")

# stunnel carries the request over a local plain-TCP socket, but Kavkom sees
# the request after TLS termination. Keep the SIP Via/Contact transport aligned
# with the public TLS service, as a real TLS softphone such as Linphone does.
SIP_VIA_TRANSPORT = "TLS" if TRANSPORT == "tls" else TRANSPORT.upper()
CONTACT_TRANSPORT = "tls" if TRANSPORT == "tls" else TRANSPORT
CONTACT = f"<sip:{USER}@{EXTERNAL_IP}:{EXTERNAL_SIP_PORT};transport={CONTACT_TRANSPORT}>"
# URI pour Digest (simplifié)
REQUEST_URI_DIGEST = f"sip:{DOMAIN}"
# URI pour la ligne REGISTER (avec port)
REQUEST_URI_LINE = f"sip:{DOMAIN}"


def md5_hex(value: str) -> str:
    return hashlib.md5(value.encode("utf-8")).hexdigest()


def sip_status(response: str) -> str:
    """Return only the SIP status line so credentials never reach logs."""
    return response.splitlines()[0] if response else "no SIP response"


def digest_failure_details(response: str, previous_nonce: str) -> str:
    """Describe a repeated Digest challenge without logging credentials."""
    stale = re.search(r'\bstale=(?:"?)(true|false)', response, re.IGNORECASE)
    nonce = re.search(r'\bnonce="([^"]+)"', response, re.IGNORECASE)
    nonce_changed = bool(nonce and nonce.group(1) != previous_nonce)
    return f"{sip_status(response)} (stale={stale.group(1).lower() if stale else 'absent'}, nonce_changed={nonce_changed})"


def read_sip_response(sock: socket.socket) -> str:
    chunks = []
    while True:
        chunk = sock.recv(4096)
        if not chunk:
            break
        chunks.append(chunk)
        if b"\r\n\r\n" in chunk:
            break
    return b"".join(chunks).decode("utf-8", errors="replace")


def build_register(
    auth_header: str | None = None,
    cseq: int = 1,
    call_id: str | None = None,
    tag: str | None = None,
) -> bytes:
    call_id = call_id or str(uuid.uuid4())
    tag = tag or uuid.uuid4().hex[:12]
    branch = f"z9hG4bK-{uuid.uuid4().hex[:12]}"
    lines = [
        f"REGISTER {REQUEST_URI_LINE} SIP/2.0",
        f"Via: SIP/2.0/{SIP_VIA_TRANSPORT} {EXTERNAL_IP}:{EXTERNAL_SIP_PORT};rport;branch={branch}",
        "Max-Forwards: 70",
        f"From: <sip:{USER}@{REALM}>;tag={tag}",
        f"To: <sip:{USER}@{REALM}>",
        f"Call-ID: {call_id}",
        f"CSeq: {cseq} REGISTER",
        f"Contact: {CONTACT}",
        f"Expires: {EXPIRES}",
    ]
    if auth_header:
        lines.append(f"Authorization: {auth_header}")
    lines.extend([
        "User-Agent: heroescrm-digest-probe",
        "Content-Length: 0",
    ])
    return ("\r\n".join(lines) + "\r\n\r\n").encode("utf-8")


def parse_digest_challenge(response: str):
    match = re.search(r'WWW-Authenticate:\s*Digest\s+(.*)', response, re.IGNORECASE | re.DOTALL)
    if not match:
        return None
    params = match.group(1)
    realm = re.search(r'realm="([^"]+)"', params)
    nonce = re.search(r'nonce="([^"]+)"', params)
    opaque = re.search(r'opaque="([^"]+)"', params)
    qop = re.search(r'qop="([^"]+)"', params)
    algorithm = re.search(r'algorithm=([^,\s]+)', params, re.IGNORECASE)
    if not realm or not nonce:
        return None
    return {
        "realm": realm.group(1),
        "nonce": nonce.group(1),
        "opaque": opaque.group(1) if opaque else None,
        # Servers may advertise a list such as "auth,auth-int". RFC 7616
        # requires selecting one supported token, not hashing the whole list.
        "qop": next((item.strip() for item in qop.group(1).split(",") if item.strip().lower() == "auth"), None) if qop else None,
        "algorithm": (algorithm.group(1) if algorithm else "MD5").upper(),
    }


def build_authorization_header(challenge, cseq: int, call_id: str) -> str:
    realm = challenge["realm"]
    nonce = challenge["nonce"]
    qop = challenge.get("qop")
    algorithm = challenge.get("algorithm", "MD5")
    opaque = challenge.get("opaque")

    nc = "00000001"
    cnonce = uuid.uuid4().hex
    uri = REQUEST_URI_DIGEST  # Utilise URI simplifié pour Digest

    ha1 = md5_hex(f"{USER}:{realm}:{PASSWORD}")
    ha2 = md5_hex(f"REGISTER:{uri}")
    if qop and qop.lower() == "auth":
        response = md5_hex(f"{ha1}:{nonce}:{nc}:{cnonce}:{qop}:{ha2}")
        auth = (
            f'username="{USER}", realm="{realm}", nonce="{nonce}", uri="{uri}", '
            f'response="{response}", algorithm={algorithm}, qop={qop}, '
            f'nc={nc}, cnonce="{cnonce}"'
        )
    else:
        response = md5_hex(f"{ha1}:{nonce}:{ha2}")
        auth = (
            f'username="{USER}", realm="{realm}", nonce="{nonce}", uri="{uri}", '
            f'algorithm={algorithm}, response="{response}"'
        )
    if opaque:
        auth += f', opaque="{opaque}"'
    # RFC 3261: the Authorization header value starts with the authentication
    # scheme. Without this `Digest` prefix, Kavkom correctly ignores the
    # computed credentials and sends a fresh 401 challenge.
    return f"Digest {auth}"


def perform_registration() -> bool:
    call_id = str(uuid.uuid4())
    tag = uuid.uuid4().hex[:12]
    initial = build_register(call_id=call_id, tag=tag)
    with socket.create_connection((TARGET_HOST, TARGET_PORT), timeout=10) as sock:
        sock.sendall(initial)
        response = read_sip_response(sock)
        if "401 Unauthorized" not in response:
            print(f"[KAVKOM] Initial REGISTER status: {sip_status(response)}")
            return False
        challenge = parse_digest_challenge(response)
        if not challenge:
            print("[KAVKOM] Could not parse Digest challenge.")
            return False
        print(f"[KAVKOM] Digest challenge: algorithm={challenge['algorithm']}, qop={challenge['qop'] or 'none'}, opaque={'yes' if challenge['opaque'] else 'no'}.")
        auth_header = build_authorization_header(challenge, 2, call_id)
        follow_up = build_register(auth_header=auth_header, cseq=2, call_id=call_id, tag=tag)
        sock.sendall(follow_up)
        response2 = read_sip_response(sock)
        if "200 OK" in response2:
            print("[KAVKOM] Registration succeeded with Digest auth.")
            return True
        print(f"[KAVKOM] Digest REGISTER status: {digest_failure_details(response2, challenge['nonce'])}")
        return False


def register_loop():
    while True:
        try:
            registered = perform_registration()
        except Exception as exc:
            print(f"[KAVKOM] Register loop error: {exc}")
            registered = False
        time.sleep(max(30, EXPIRES // 2) if registered else 10)


if __name__ == "__main__":
    print(f"[KAVKOM] Starting registrar for {USER}@{REALM} via {TARGET_HOST}:{TARGET_PORT}")
    register_loop()
