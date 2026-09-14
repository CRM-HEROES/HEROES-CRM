#!/bin/bash
echo "=== DIAGNOSTIC REGISTER KAVKOM ==="
echo ""
echo "1. Variables d'environnement KAVKOM:"
docker exec telephony-freeswitch env | grep -i kavkom

echo ""
echo "2. Processus Python de REGISTER:"
docker exec telephony-freeswitch ps aux | grep "kavkom-register\|python" | grep -v grep

echo ""
echo "3. Configuration FreeSWITCH Kavkom:"
docker exec telephony-freeswitch cat /etc/freeswitch/sip_profiles/external/kavkom.xml

echo ""
echo "4. Inclusion dans external.xml:"
docker exec telephony-freeswitch cat /etc/freeswitch/sip_profiles/external.xml | grep -i "include"

echo ""
echo "5. Logs FreeSWITCH (dernières 50 lignes):"
docker exec telephony-freeswitch tail -50 /var/log/freeswitch/freeswitch.log 2>/dev/null || echo "Log file not found"

echo ""
echo "6. Teste la connectivité stunnel (TCP 15062):"
docker exec telephony-freeswitch sh -c "timeout 2 nc -zv 127.0.0.1 15062 2>&1 || echo 'Port 15062 not reachable'"

echo ""
echo "7. Stunnel status:"
docker exec telephony-freeswitch ps aux | grep stunnel | grep -v grep

echo ""
echo "8. ESL (Event Socket):"
docker exec telephony-freeswitch ss -lntp 2>/dev/null | grep 8021 || echo "ESL port 8021 not found"
