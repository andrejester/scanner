#!/bin/bash

# Konfigurasi RabbitMQ
RABBITMQ_USER	="Rivaldo"
RABBITMQ_PASS	="rivaldo"
RABBITMQ_HOST	="rabbitmq.sis2.net"
TARGET_VHOST	="switching-mmodal"

#echo "Menutup koneksi RabbitMQ di vhost 'switching-mmodal'..."

# Ambil daftar koneksi dari RabbitMQ API
RESPONSE=$(curl -s -u $RABBITMQ_USER:$RABBITMQ_PASS "http://$RABBITMQ_HOST:15672/api/connections")

# Ambil hanya koneksi yang memiliki vhost "switching-mmodal" dengan jq
CONNECTIONS=$(echo "$RESPONSE" | jq -r '.[] | select(.vhost=="'"$TARGET_VHOST"'") | .name | @uri')

# Debug: Pastikan daftar koneksi sudah benar
#echo "=== Daftar koneksi yang ditemukan di vhost '$TARGET_VHOST' ==="
#echo "$CONNECTIONS"
#echo "========================================================="

# Jika tidak ada koneksi, hentikan proses
#if [[ -z "$CONNECTIONS" ]]; then
#    echo "Tidak ada koneksi yang ditemukan untuk vhost '$TARGET_VHOST'."
#    exit 0
#fi

# Loop untuk menutup hanya koneksi di vhost 'switching-mmodal'
echo "$CONNECTIONS" | while read -r conn; do
    echo "Menutup koneksi: $conn"
    
    # Kirim request DELETE tanpa mengubah format koneksi
    curl -X DELETE -u "$RABBITMQ_USER:$RABBITMQ_PASS" "http://$RABBITMQ_HOST:15672/api/connections/$conn"
done

echo "Menunggu 5 detik sebelum restart..."
sleep 5

echo "Memulai ulang rcp_server.php..."
#DEV
#nohup php /var/www/prg/app/assist-bpr.net/bin/rabbit/rcp_server.php > /dev/null 2>&1 &
#PRO
nohup php /var/www/prg/app/mvc/assist-bpr.net/bin/rabbit/rcp_server.php > /dev/null 2>&1 &