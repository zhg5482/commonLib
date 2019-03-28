# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://poser.pugx.org/laravel/lumen-framework/d/total.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/lumen-framework/v/stable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/lumen-framework/v/unstable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://poser.pugx.org/laravel/lumen-framework/license.svg)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## commonLib

## nginx

## rabbitMq

参考文档 https://www.rabbitmq.com/
1.安装
    brew install rabbitmq
2.http://localhost:15672
3.composer 
"require": {
        "php-amqplib/php-amqplib": "2.6.*"
    },
    
## FastDfs

##### 参考文档：https://blog.csdn.net/u012979009/article/details/55052318
#####  1.安装
######  https://blog.csdn.net/xifeijian/article/details/385678392
#####  2.启动 /user/bin
######  启动tracker: /usr/local/bin/fdfs_trackerd /etc/fdfs/tracker.conf
######  启动storage: /usr/local/bin/fdfs_storaged /etc/fdfs/storage.conf
#####  3.配置 /etc/fdfs
#####  4.nginx 源码 fastdfs-nginx-module 模块安装
######  --prefix=/usr/local/Cellar/nginx/1.15.8 --sbin-path=/usr/local/Cellar/nginx/1.15.8/bin/nginx --with-cc-opt='-I/usr/local/opt/pcre/include -I/usr/local/opt/openssl/include' --with-ld-opt='-L/usr/local/opt/pcre/lib -L/usr/local/opt/openssl/lib' --conf-path=/usr/local/etc/nginx/nginx.conf --pid-path=/usr/local/var/run/nginx.pid --lock-path=/usr/local/var/run/nginx.lock --http-client-body-temp-path=/usr/local/var/run/nginx/client_body_temp --http-proxy-temp-path=/usr/local/var/run/nginx/proxy_temp --http-fastcgi-temp-path=/usr/local/var/run/nginx/fastcgi_temp --http-uwsgi-temp-path=/usr/local/var/run/nginx/uwsgi_temp --http-scgi-temp-path=/usr/local/var/run/nginx/scgi_temp --http-log-path=/usr/local/var/log/nginx/access.log --error-log-path=/usr/local/var/log/nginx/error.log --with-debug --with-http_addition_module --with-http_auth_request_module --with-http_dav_module --with-http_degradation_module --with-http_flv_module --with-http_gunzip_module --with-http_gzip_static_module --with-http_mp4_module --with-http_random_index_module --with-http_realip_module --with-http_secure_link_module --with-http_slice_module --with-http_ssl_module --with-http_stub_status_module --with-http_sub_module --with-http_v2_module --with-ipv6 --with-mail --with-mail_ssl_module --with-pcre --with-pcre-jit --with-stream --with-stream_realip_module --with-stream_ssl_module --with-stream_ssl_preread_module --add-module=/Users/services/fastdfs-nginx-module/src
#####  5.数据 /Users/services/data/fastdfs-storage/data
#####  6.nginx 配置 commonlibFile.conf
#####  7.建立软连接
######  ln -s /home/wwwroot/default/data  /home/wwwroot/default/data/M00

## Elasticsearch

1.安装
brew install elasticsearch
参考：https://blog.csdn.net/u014082714/article/details/86409774
2.composer api库
{
    "require": {
        "elasticsearch/elasticsearch": "~6.0"
    }
}
参考：https://www.elastic.co/guide/cn/elasticsearch/php/current/_quickstart.html
3.实例化一个客户端 
require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()->build();
4.安装elasticsearch-head插件
    安装 node   
        brew install node
    下载插件并安装
        git clone git://github.com/mobz/elasticsearch-head.git
        cd elasticsearch-head
        npm install
        安装完成后在elasticsearch-head/node_modules目录下会出现grunt文件。
        如果没有grunt二进制程序，需要执行
        cd elasticsearch-head
        npm install grunt --save
    修改服务器监听地址
    修改elasticsearch-head下Gruntfile.js文件，默认监听在127.0.0.1下9200端口
    connect: {
            server: {
                options: {
                    hostname: '*',
                    port: 9100,//注：elasticsearch-head监听 9100 端口
                    base: '.',
                    keepalive: true
                }
            }
        }  
    修改连接地址
    cd elasticsearch-head/_site
    vim app.js
    this.base_uri = this.config.base_uri || this.prefs.get("app-base_uri") || "http://localhost:9200";
    在cd elasticsearch-head目录下运行
    ../elasticsearch-head/node_modules/grunt/bin/grunt server
    访问显示"未连接"
        解决方案：    
        vim $ES_HOME$/config/elasticsearch.yml
        由于我采用brew安装的ES所以$ES_HOME$/config为/usr/local/etc/elasticsearch/
        增加如下字段        
        http.cors.enabled: true
        http.cors.allow-origin: "*"
        重启es，并刷新head页面，发现已经可以连接上。   
        使用brew install安装es后的一些安装路径：
        elasticsearch:  /usr/local/Cellar/elasticsearch/6.2.4
        Data:    /usr/local/var/elasticsearch/elasticsearch_xuchen/
        Logs:    /usr/local/var/log/elasticsearch/elasticsearch_xuchen.log
        Plugins: /usr/local/opt/elasticsearch/libexec/plugins/
        Config:  /usr/local/etc/elasticsearch/
        plugin script: /usr/local/opt/elasticsearch/libexec/bin/elasticsearch-plugin
注：先启动 es  ./elasticsearch  再启动 elasticsearch-head    ../elasticsearch-head/node_modules/grunt/bin/grunt server
elasticsearch-head:http://localhost:9100/ .  elasticsearch:http://localhost:9200/
参考文档：https://elasticsearch.cn/book/elasticsearch_definitive_guide_2.x/
5.logstash 同步mysql数据到elasticsearch
6.lib/elasticSearch

