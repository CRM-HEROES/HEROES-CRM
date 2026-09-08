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
TRANSPORT = os.environ.get("KAVKOM_SIP_TRANSPORT", "tls")
KAVKOM_PROXY_PORT = os.environ.get("KAVKOM_SIP_PORT", "5061")

CONTACT = f"<sip:{USER}@{EXTERNAL_IP}:{EXTERNAL_SIP_PORT};transport=tcp>"
REQUEST_URI = f"sip:{DOMAIN}:{KAVKOM_PROXY_PORT};transport=tcp"


def md5_hex(value: str) -> str:
    return hashlib.md5(value.encode("utf-8")).hexdigest()


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
        f"REGISTER {REQUEST_URI} SIP/2.0",
        "Via: SIP/2.0/TCP " + f"{EXTERNAL_IP}:{EXTERNAL_SIP_PORT};rport;branch={branch}",
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
    qop = re.search(r'qop="([^"]+)"', params)
    algorithm = re.search(r'algorithm=([^,\s]+)', params, re.IGNORECASE)
    if not realm or not nonce:
        return None
    return {
        "realm": realm.group(1),
        "nonce": nonce.group(1),
        "qop": qop.group(1) if qop else None,
        "algorithm": (algorithm.group(1) if algorithm else "MD5").upper(),
    }


def build_authorization_header(challenge, cseq: int, call_id: str) -> str:
    realm = challenge["realm"]
    nonce = challenge["nonce"]
    qop = challenge.get("qop")
    algorithm = challenge.get("algorithm", "MD5")

    nc = "00000001"
    cnonce = uuid.uuid4().hex
    uri = REQUEST_URI

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
    return auth


def perform_registration() -> bool:
    call_id = str(uuid.uuid4())
    tag = uuid.uuid4().hex[:12]
    initial = build_register(call_id=call_id, tag=tag)
    with socket.create_connection((TARGET_HOST, TARGET_PORT), timeout=10) as sock:
        sock.sendall(initial)
        response = read_sip_response(sock)
        if "401 Unauthorized" not in response:
            print(f"[KAVKOM] Initial REGISTER did not get challenge: {response[:500]}")
            return False
        challenge = parse_digest_challenge(response)
        if not challenge:
            print(f"[KAVKOM] Could not parse digest challenge: {response[:500]}")
            return False
        auth_header = build_authorization_header(challenge, 2, call_id)
        follow_up = build_register(auth_header=auth_header, cseq=2, call_id=call_id, tag=tag)
        sock.sendall(follow_up)
        response2 = read_sip_response(sock)
        if "200 OK" in response2:
            print("[KAVKOM] Registration succeeded with Digest auth.")
            return True
        print(f"[KAVKOM] Registration failed: {response2[:800]}")
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
