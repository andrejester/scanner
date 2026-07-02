#!/bin/bash

URL="http://bpr.bepede.sis1.net/api/atm_bepede"

while true
do
    echo "=== Request at $(date) ==="
    curl -i "$URL"
    echo -e "\n---------------------------\n"
    sleep 3
done
