<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {

        if ($exception instanceof MethodNotAllowedHttpException) {
            echoToJson('Incorrect access',array());
        }
        if($exception instanceof \Symfony\Component\Debug\Exception\FatalErrorException && !config('app.debug')) {//加上app.debug防止dubug关闭模式下暴露重要信息
            echoToJson('Incorrect access',array());
        }
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
            if (view()->exists('errors.' . $code)) {
                $message  = $exception->getMessage();
                return response()->view('errors.' . $exception->getStatusCode(), ['message'=>$message], $exception->getStatusCode());
            }
        }
        if ($exception instanceof ModelNotFoundException)
        {
            echoToJson('Incorrect access',array());
        }
        if ($exception instanceof \ErrorException){
            echoToJson('Parameter error',array());
        }
        return parent::render($request, $exception);
    }
}
