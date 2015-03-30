#朋友圈文档

##架构

###框架
采用 YAF 作为开发框架.
框架文档: http://www.laruence.com/manual/

###存储
采用 SSDB 作为永久存储引擎.
文档: http://ssdb.io/zh_cn/

###配置

配置文件地址 : $root/conf/application.ini

```
ssdb.master.host = '127.0.0.1'
ssdb.master.port = 8888
ssdb.master.timeout = 2000
ssdb.master.easy = 1

ssdb.slave.host = '127.0.0.1'
ssdb.slave.port = 8889
ssdb.slave.timeout = 2000
ssdb.slave.easy = 1
```