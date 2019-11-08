<?php
/**
 * 多进程测试
 */
$pid_arr = [];
for ($i = 0; $i < 3; $i++){
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("开启进程失败");
    } elseif ($pid) {
        echo "启动子进程 $pid \n";
        array_push($pid_arr, $pid);
    } else {
        echo "子进程 ".getmypid()." 正在处理任务\n";
        task(getmypid());
        exit;
    }
}

function task($pid) {
    sleep(10);
    file_put_contents('a.txt',$pid.'_'.date('Y-m-d H:i:s',time()).PHP_EOL,FILE_APPEND);
}

for ($i=0; $i < count($pid_arr); $i++) {
    while (pcntl_waitpid($pid_arr[$i], $status) != -1) {
        if(!pcntl_wifexited($status)){
            //进程非正常退出
            if(pcntl_wifsignaled($status)){
                $signal = pcntl_wtermsig($status);
                //不是通过接受信号中断
                echo "子进程 $pid_arr[$i] 属于非正常停止，接收到信号 $signal \n";
            }else{
                print_r("子进程 $pid_arr[$i] 完成任务并退出 \n");
            }

        }else{
            //获取进程终端的退出状态码;
            $code = pcntl_wexitstatus($status);
            print_r("子进程 $pid_arr[$i] 正常结束任务并退出，状态码 $status \n ");
        }
    }
}
