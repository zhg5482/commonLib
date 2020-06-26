<?php

declare(ticks=1);
$max = 3;
$child = 1;
function sig_handler($sig){
    global $child;
    switch ($sig){
        case SIGCHLD:
            echo 'sigchld received'."\n";
            $child --;
            break;
    }
}
pcntl_signal(SIGCHLD,'sig_handler'); //SIGCHLD 安装信号 子进程结束时候调用

while (true){
    $child ++;
    /**
     * pcntl_fork 函数返回2个值 一个为0：表示为子进程，一个为正整数，表示为子进程ID 区别为：父进程在执行时候返回为正整数即子进程ID，子进程在执行时候返回为0
     */
    $pid = pcntl_fork();
    if($pid){     //父进程
        if($child > $max){
            //如果子进程数超过了最大值，则挂起父进程
            pcntl_wait($status);
        }
    }else{         //子进程 可以用pcntl_exec执行其他代码
        echo "starting new child | now we have {$child} child process\n";
        sleep(rand(3,5));
        posix_kill(posix_getpid(), SIGUSR1);
        exit();
    }
}



