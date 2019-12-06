<?php
declare(ticks = 1);
$arrint = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15];//假设很多
$arrint = array_chunk($arrint,4,TRUE);//把数组分为4个

// 创建消息队列,以及定义消息类型(类似于数据库中的库)
$id = ftok(__FILE__,'m');//生成文件key，唯一
$msgQueue = msg_get_queue($id);
const MSG_TYPE = 1;
msg_send($msgQueue,MSG_TYPE,'0');//给消息队列一个默认值0，必须是字符串类型

//fork出四个子进程
for ($i = 0; $i < 4; $i++){
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("could not fork");
    } elseif ($pid) {
        echo $pid;
        echo "I'm the Parent $i\n";
    } else {
        // 子进程处理逻辑，相互独立，解决办法，放到内存消息队列中
        $part = array_sum($arrint[$i]);
        implode_sum($part);//合成计算出的sum
        exit;// 一定要注意退出子进程,否则pcntl_fork() 会被子进程再fork,带来处理上的影响。
    }
}

function implode_sum($part){
    global $msgQueue;
    msg_receive($msgQueue,MSG_TYPE,$msgType,1024,$sum);//获取消息队列中的值，最后一个参数为队列中的值
    $sum = intval($sum) + $part;
    msg_send($msgQueue,MSG_TYPE,$sum);//发送每次计算的结果给消息队列
}

// 等待子进程执行结束
while (pcntl_waitpid(0, $status) != -1) {
    $status = pcntl_wexitstatus($status);
    $pid = posix_getpid();
    //posix_kill($pid, SIGUSR1);
    echo "Child $status completed\n";
}

//信号处理函数
function sig_handler($signo)
{
    switch ($signo) {
        case SIGTERM:
            // 处理SIGTERM信号
            exit;
            break;
        case SIGHUP:
            //处理SIGHUP信号
            break;
        case SIGUSR1:
            echo "Caught SIGUSR1...\n";
            break;
        default:
            // 处理所有其他信号
    }

}

echo "Installing signal handler...\n";

//安装信号处理器
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP,  "sig_handler");
pcntl_signal(SIGUSR1, "sig_handler");


//所有子进程结束后，再取出最后在队列中的值，就是int数组的和
msg_receive($msgQueue,MSG_TYPE,$msgType,1024,$sum);
echo $sum;//输出120
