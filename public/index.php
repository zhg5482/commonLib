<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

/*
|--------------------------------------------------------------------------
| Create The Application(创建应用程序)
|--------------------------------------------------------------------------
| 首先我们需要一个应用实例
| 这将创建一个应用程序的实例/容器和应用程序准备好接受HTTP/控制台从环境要求
|
*/
$app = require __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application(运行应用程序)
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request 一旦我们有了应用程序
| through the kernel, and send the associated response back to 我们可以通过内核处理传入的请求
| the client's browser allowing them to enjoy the creative  并将相关的响应发送回客户机的浏览器
| and wonderful application we have prepared for them. 让他们享受我们为他们准备的创造性和奇妙的应用程序
|
*/

$app->run();
