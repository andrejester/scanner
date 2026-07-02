#!/bin/bash

echo "Menunggu 5 detik sebelum restart..."
sleep 5

echo "Memulai ulang rcp_server.php..."
nohup php /var/www/prg/app/assist-bpr.net/bin/rabbit/rcp_server.php > /dev/null 2>&1 &
