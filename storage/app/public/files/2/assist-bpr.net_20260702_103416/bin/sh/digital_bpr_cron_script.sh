#!/bin/bash

while true; do
	php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_bpr_h2hcurl.php $1 &
	sleep 5
done
