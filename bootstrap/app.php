<?php

//载入自动加载文件
require_once __DIR__.'/../vendor/autoload.php';

// 加载环境变量 Dotenv 使用 .env配置
(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application 创建应用程序
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance 在这里，我们将加载环境并创建作为该框架的中心部分的应用程序实例
| that serves as the central piece of this framework. We'll use this 我们将使用这个应用程序作为这个框架的"IOC"容器和路由
| application as an "IoC" container and router for this framework.
|
*/
$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

//为应用程序注册门面
$app->withFacades();
//为应用程序加载功能强大的库
//$app->withEloquent();

//加载配置
$app->configure('app');
$app->configure('database');
$app->configure('queue');
$app->configure('apiService');
$app->configure('elasticsearch');
$app->configure('vueRoutes');

/*
|--------------------------------------------------------------------------
| Register Container Bindings 登记容器绑定
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/
$app->singleton( //异常处理程序
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton( // 控制台内核
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);
//路由中间件
$app->routeMiddleware([
    'signVerification' => App\Http\Middleware\SignVerification::class,   //通用接口权限验证
    'vueVerification' => App\Http\Middleware\VueVerification::class,   //vue后台管理接口权限验证
    'throttle' => App\Http\Middleware\ThrottleRequests::class,  //控制接口访问频次中间件
]);
/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);
//注册服务
$app->register(Illuminate\Redis\RedisServiceProvider::class);   //注册redis
$app->register(Jenssegers\Mongodb\MongodbServiceProvider::class);  //注册mongodb
$app->register(Wn\Generators\CommandsServiceProvider::class); //增加artisan命令

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

/*
 * 配置日志文件为每日[保存文件/保存到mongodb]
 */
//$app->configureMonologUsing(function (Monolog\Logger $monolog) {
//    $mongoHandler = new \Monolog\Handler\MongoDBHandler(
//        new \MongoDB\Client('mongodb://lumen:lumen@localhost:27017/lumen'), 'lumen', 'log'
//    );
//
//    return $monolog->pushHandler($mongoHandler);
//});
//
//$app->configureMonologUsing(function(Monolog\Logger $monoLog) use ($app){
//    return $monoLog->pushHandler(
//        new \Monolog\Handler\RotatingFileHandler($app->storagePath().'/logs/lumen.log',5)
//    );
//});

//路由实现
$app->router->group(['namespace' => 'App\Http\Controllers',], function ($router) {
    require __DIR__.'/../routes/web.php';
});

//返回应用实例
return $app;
