#!/bin/bash
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_tabungannominatif.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_kreditnominatif.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_system.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_tabungancadangan.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_riset.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_asset.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_pembebananbungatabungan.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_audit.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_depositocadanganharian.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_tabungancadanganharian.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_tabungandormant.php $1 &
php /var/www/prg/app/mvc/assist-bpr.net/bin/crontab/crontab_depositobungaotomatis.php $1 &