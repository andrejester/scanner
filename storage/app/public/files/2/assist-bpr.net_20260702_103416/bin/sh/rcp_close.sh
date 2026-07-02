#!/bin/bash

# Konfigurasi RabbitMQ
RABBITMQ_USER="Rivaldo"
RABBITMQ_PASS="rivaldo"
RABBITMQ_HOST="rabbitmq.sis2.net"
TARGET_VHOST="switching-mmodal"

# Ambil daftar koneksi dari RabbitMQ API
RESPONSE=$(curl -s -u $RABBITMQ_USER:$RABBITMQ_PASS "http://$RABBITMQ_HOST:15672/api/connections")

# Ambil hanya koneksi yang memiliki vhost "switching-mmodal" dengan jq
CONNECTIONS=$(echo "$RESPONSE" | jq -r '.[] | select(.vhost=="'"$TARGET_VHOST"'") | .name | @uri')

# Loop untuk menutup hanya koneksi di vhost 'switching-mmodal'
echo "$CONNECTIONS" | while read -r conn; do
    echo "Menutup koneksi: $conn"
    curl -X DELETE -u "$RABBITMQ_USER:$RABBITMQ_PASS" "http://$RABBITMQ_HOST:15672/api/connections/$conn"
done

echo "Semua koneksi di vhost '$TARGET_VHOST' telah ditutup pada pukul 17:00."
