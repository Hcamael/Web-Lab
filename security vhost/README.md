# 虚拟主机安全部署(基于LNMP)

对自己服务器上的不同虚拟主机进行安全隔离有以下几个要点:

## 目录隔离：.user.ini
网站跟目录下的该文件最好别删除，如果想删也行
```
$ chattr -i .user.ini
$ rm .user.ini
```

你可以`$ cat .user.ini`，查看该文件，里面默认存了三个路径，当前路径，`tmp`，`/proc`，作用就是在该目录下的php脚本只能访问这三个路径下的文件

不过限制的只是php，如果用`system`函数执行shell命令，却无影响，不过lnmp中`php.ini` 默认禁用执行系统命令的函数，具体有啥可自己自己去查看`$ cat /usr/local/php/etc/php.ini | grep disable_functions`

## 权限限制
一个虚拟主机一个用户

基于这些我写了一个一键部署虚拟主机脚本`add.sh`, 具体细节自己读代码
在这之前需要修改几个文件
```
$ vim /etc/init.d/php-fpm
vhost=$2
if [ -n "$vhost" ];then
        vhost=-$vhost
fi

php_fpm_CONF=${prefix}/etc/php-fpm$vhost.conf
php_fpm_PID=${prefix}/var/run/php-fpm$vhost.pid


restart)
                $0 stop $2
                $0 start $2
```

```
$ vim /bin/lnmp
        #include enable-php.conf;
        location ~ [^/]\.php(/|$)
        {
            # comment try_files $uri =404; to enable pathinfo
            try_files $uri =404;
            fastcgi_pass  unix:/tmp/php-cgi.sock;
            fastcgi_index index.php;
            include fastcgi.conf;
            #include pathinfo.conf;
        }
```

脚本之后如果对域名和端口有特殊需求，请自行修改`/usr/local/nginx/conf/vhost/$1.conf`

## 数据库的权限设置
php和服务器设置完后，还需对数据库进行相应设置

建议一个站点一个用户，该用户只对该站的数据库有相应的权限

**PS: 最好最好最好别使用root用户**

还建议修改`my.cnf`
```
$ vim /etc/my.cnf
[mysqld]
bind-address= 127.0.0.1
```

这样数据库就不对外网开放了

附：测试过程中有什么需要查看数据库查询语句细节，也可以修改`my.cnf`
```
$ vim /etc/my.cnf
[mysqld]
general_log = 1
# general_log_file =
```
