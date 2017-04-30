/usr/bin/xtail /qruqsp/sites/qruqsp.local/logs/20*.log | /usr/bin/php5 /qruqsp/sites/qruqsp.local/site/qruqsp-mods/aprs/scripts/inject.php &
/usr/local/bin/rtl_fm -f 144.39M - | /usr/local/bin/direwolf -c /home/pi/direwolf.conf -l /qruqsp/sites/qruqsp.local/logs -r 24000 -D 1
# this works for Andrew
# /usr/local/bin/rtl_fm -f 144.39M -p 56 - | /usr/local/bin/direwolf -c /home/pi/direwolf.conf -l /qruqsp/sites/qruqsp.local/logs -r 24000 -D 1
