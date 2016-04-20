if [ ! -n "$1" ];then
        echo "Please enter vhost name";
        exit 1
fi
groupadd $1
useradd $1 -M -s /sbin/nologin -g $1
touch /usr/local/php/var/php-fpm-$1.pid
touch /tmp/php-cgi-$1.sock
chown www:www /tmp/php-cgi-$1.sock
cp /usr/local/php/etc/php-fpm.conf /usr/local/php/etc/php-fpm-$1.conf
sed -i "s/www/$1/g" /usr/local/php/etc/php-fpm-$1.conf
sed -i "s/php-fpm/php-fpm-$1/g" /usr/local/php/etc/php-fpm-$1.conf
sed -i "s/php-cgi/php-cgi-$1/g" /usr/local/php/etc/php-fpm-$1.conf
echo $1 | lnmp vhost add
sed -i "s/php-cgi/php-cgi-$1/g" /usr/local/php/etc/php-fpm-$1.conf /usr/local/nginx/conf/vhost/$1.conf
/etc/init.d/php-fpm start $1