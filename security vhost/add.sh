#!/bin/sh
if [ ! -n "$1" ];then
        echo "Please enter vhost name";
        exit 1
fi
groupadd $1
useradd $1 -M -s /sbin/nologin -g $1

cat >/usr/local/php/etc/php-fpm-$1.conf<<EOF
[global]
pid = /usr/local/php/var/run/php-fpm-$1.pid
error_log = /usr/local/php/var/log/php-fpm-$1.log
log_level = notice

[$1]
listen = /tmp/php-cgi-$1.sock
#listen = 127.0.0.1:9000
listen.backlog = -1
listen.allowed_clients = 127.0.0.1
listen.owner = $1
listen.group = $1
listen.mode = 0666
user = $1
group = $1
pm = dynamic
;pm = static
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 6
request_terminate_timeout = 100
request_slowlog_timeout = 0
slowlog = var/log/slow-$1.log
EOF

echo $1 | lnmp vhost add
chown $1:$1 -R /home/wwwroot/$1
sed -i "s/php-cgi/php-cgi-$1/g" /usr/local/nginx/conf/vhost/$1.conf
/etc/init.d/php-fpm restart $1
/etc/init.d/nginx restart
