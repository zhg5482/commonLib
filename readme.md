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

## 中文文档
    https://learnku.com/docs/lumen/5.7
## commonLib
    公共库
## nginx
    配置文件 根目录 *.conf
    
## serviceId 生成规则
    app/helpers.php makeServiceId()

## verificationSign 签名验证
    app/helpers.php verificationSign()

## throttle 访问频次控制
    App\Http\Middleware\ThrottleRequests.php

## redis 
    php 扩展安装
    composer predis/predis 安装vender包
    
## mongodb
    mongo 扩展安装
    composer jenssegers/mongodb 安装vender包
    参考 ： http://returnc.com/detail/3728
## rabbitMq

##### 参考文档 https://www.rabbitmq.com/
##### 1.安装
    brew install rabbitmq
##### 2.http://localhost:15672
##### 3.composer 
    "require": {
        "php-amqplib/php-amqplib": "2.6.*"
    }
##### 4.配置 config/queue.php
   
## FastDfs

#####  参考文档：https://blog.csdn.net/u012979009/article/details/55052318
#####  1.安装
      php扩展 ： fastdfs_client.so
      https://blog.csdn.net/xifeijian/article/details/385678392
#####  2.配置 /etc/fdfs
        tracker.conf
            base_path，改为自己的工作目录。
        storage.conf[可配置多个 例如 storage-test.conf、storage-prod.conf]
            port，监听端口号
            base_path，改为自己的工作目录
            store_path_count，如果多目录这里可以改
            store_path1，如果多目录这里数字可以往后排
            tracker_server，tracker服务器的地址和端口号
            group_name，这里是组名，用于在上传和下载的时候区分不同的组。
        mod_fastdfs.conf
            base_path，工作目录
            tracker_server，tracker服务器地址
            group_name，如果是多个groupName，所以用斜线分隔开，例如：test/prod
            group_count，两个组，所以这里是2
            然后配置区分两个组的地方
#####  3.启动 /user/bin
      启动tracker: /usr/local/bin/fdfs_trackerd /etc/fdfs/tracker.conf
      启动storage: /usr/local/bin/fdfs_storaged /etc/fdfs/storage.conf      
#####  4.nginx 源码 fastdfs-nginx-module 模块安装
      --prefix=/usr/local/Cellar/nginx/1.15.8 --sbin-path=/usr/local/Cellar/nginx/1.15.8/bin/nginx --with-cc-opt='-I/usr/local/opt/pcre/include -I/usr/local/opt/openssl/include' --with-ld-opt='-L/usr/local/opt/pcre/lib -L/usr/local/opt/openssl/lib' --conf-path=/usr/local/etc/nginx/nginx.conf --pid-path=/usr/local/var/run/nginx.pid --lock-path=/usr/local/var/run/nginx.lock --http-client-body-temp-path=/usr/local/var/run/nginx/client_body_temp --http-proxy-temp-path=/usr/local/var/run/nginx/proxy_temp --http-fastcgi-temp-path=/usr/local/var/run/nginx/fastcgi_temp --http-uwsgi-temp-path=/usr/local/var/run/nginx/uwsgi_temp --http-scgi-temp-path=/usr/local/var/run/nginx/scgi_temp --http-log-path=/usr/local/var/log/nginx/access.log --error-log-path=/usr/local/var/log/nginx/error.log --with-debug --with-http_addition_module --with-http_auth_request_module --with-http_dav_module --with-http_degradation_module --with-http_flv_module --with-http_gunzip_module --with-http_gzip_static_module --with-http_mp4_module --with-http_random_index_module --with-http_realip_module --with-http_secure_link_module --with-http_slice_module --with-http_ssl_module --with-http_stub_status_module --with-http_sub_module --with-http_v2_module --with-ipv6 --with-mail --with-mail_ssl_module --with-pcre --with-pcre-jit --with-stream --with-stream_realip_module --with-stream_ssl_module --with-stream_ssl_preread_module --add-module=/Users/services/fastdfs-nginx-module/src
#####  5.数据 /Users/services/data/fastdfs-storage/data
#####  6.nginx 配置 commonlibFile.conf
#####  7.建立软连接
      ln -s /home/wwwroot/default/data  /home/wwwroot/default/data/M00
#####  8.api  https://www.cnblogs.com/xinyaoxp/archive/2013/10/29/3394574.html
#####  9.多机配置   https://www.cnblogs.com/cpy-devops/p/6105845.html
## Elasticsearch

#####   1.安装
        brew install elasticsearch
######  参考：https://blog.csdn.net/u014082714/article/details/86409774
#####   2.composer api库
        {
            "require": {
                "elasticsearch/elasticsearch": "~6.0"
            }
        }
#####   参考：https://www.elastic.co/guide/cn/elasticsearch/php/current/_quickstart.html
#####   3.实例化一个客户端 
        use Elasticsearch\ClientBuilder;
        $client = ClientBuilder::create()->build();
#####   4.安装elasticsearch-head插件
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
#####   注：先启动 es  ./elasticsearch  再启动 elasticsearch-head    ../elasticsearch-head/node_modules/grunt/bin/grunt server
#####       elasticsearch-head:http://localhost:9100/ .  elasticsearch:http://localhost:9200/
#####   参考文档：https://elasticsearch.cn/book/elasticsearch_definitive_guide_2.x/
#####   5.logstash 同步mysql数据到elasticsearch
#####   6.lib/elasticSearch


## 微信公众号
    1.安装：composer require overtrue/wechat:~4.0 -vvv
    2.参考文档 : https://www.easywechat.com
## swoole 
    php 扩展安装 swoole.so
    文档：
        https://wiki.swoole.com/
    lumen-swoole 文档:
        https://breeze2.github.io/lumen-swoole-http/#/?id=lumen-swoole-http
        
    rpc 文档：
        https://wiki.swoole.com/wiki/page/683.html
## logstash

    caanl 地址 https://github.com/alibaba/canal 
    logstash 地址 https://www.elastic.co/downloads/logstash
    
## grpc 
    php 扩展安装 grpc.so
    官网 ：https://grpc.io/
    中文文档 ： http://doc.oschina.net/grpc?t=57966
    "require": {
            "grpc/grpc": "^1.6",
            "google/protobuf": "v3.5.0.1"
    }
    
## ffmpeg
    官方文档 ： http://ffmpeg.org/
    mac 安装： brew install ffmpeg
    php composer 安装 composer require php-ffmpeg/php-ffmpeg
    参考文档 : https://www.cnblogs.com/peteremperor/p/6477743.html
    实例代码:App\Lib\FFmPeg\FFmPegHelper.php
                 

    
## supervisor 进程监控
    官方文档 ： http://supervisord.org/
    
    
    --prefix=/usr/local/Cellar/nginx/1.15.8 --sbin-path=/usr/local/Cellar/nginx/1.15.8/bin/nginx --with-cc-opt='-I/usr/local/opt/pcre/include -I/usr/local/opt/openssl/include' --with-ld-opt='-L/usr/local/opt/pcre/lib -L/usr/local/opt/openssl/lib' --conf-path=/usr/local/etc/nginx/nginx.conf --pid-path=/usr/local/var/run/nginx.pid --lock-path=/usr/local/var/run/nginx.lock --http-client-body-temp-path=/usr/local/var/run/nginx/client_body_temp --http-proxy-temp-path=/usr/local/var/run/nginx/proxy_temp --http-fastcgi-temp-path=/usr/local/var/run/nginx/fastcgi_temp --http-uwsgi-temp-path=/usr/local/var/run/nginx/uwsgi_temp --http-scgi-temp-path=/usr/local/var/run/nginx/scgi_temp --http-log-path=/usr/local/var/log/nginx/access.log --error-log-path=/usr/local/var/log/nginx/error.log --with-debug --with-http_addition_module --with-http_auth_request_module --with-http_dav_module --with-http_degradation_module --with-http_flv_module --with-http_gunzip_module --with-http_gzip_static_module --with-http_mp4_module --with-http_random_index_module --with-http_realip_module --with-http_secure_link_module --with-http_slice_module --with-http_ssl_module --with-http_stub_status_module --with-http_sub_module --with-http_v2_module --with-ipv6 --with-mail --with-mail_ssl_module --with-pcre --with-pcre-jit --with-stream --with-stream_realip_module --with-stream_ssl_module --with-stream_ssl_preread_module --add-module=/Users/services/fastdfs-nginx-module/src/ --add-module=/Users/services/nginx-rtmp-module/ 
    
## guzzle http请求处理
    官方文档 ： https://guzzle-cn.readthedocs.io/zh_CN/latest/index.html
    
##  Swagger
    参考文档 : https://www.imooc.com/article/71842
    
## Socialite 社会化登录
    参考文档 ： https://learnku.com/docs/laravel/5.5/socialite/1347
    
## Hprose rpc解决方案
    官方文档 ： https://hprose.com/
