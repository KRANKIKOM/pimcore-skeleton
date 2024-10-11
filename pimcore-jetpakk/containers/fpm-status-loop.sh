#!/bin/sh
if [ -z "$FCGI_HOST" ]; then
    if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then
        FCGI_HOST="localhost"
    else
        FCGI_HOST="php"
    fi
fi

FCGI_HOSTPORT="$FCGI_HOST:9000"

sleep 15
echo "FPM DEBUG: Dumping open ports"
netstat -an | grep LISTEN

while true
do
    echo "FPM DEBUG: STATUS LOOP: Getting status for fpm via fastcgi - host/port: $FCGI_HOSTPORT"
    SCRIPT_FILENAME=/status SCRIPT_NAME=/status DOCUMENT_ROOT=/var/www/html/ REQUEST_METHOD=GET cgi-fcgi -bind -connect $FCGI_HOSTPORT    
    sleep 30
done