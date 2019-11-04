<?php
/**
 * Created by PhpStorm.
 * User: zhg5482
 * Date: 2019/3/28
 * Time: 下午3:04
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Lib\File\FFmpegHelper\Coordinate\Dimension;
use App\Lib\File\FFmpegHelper\FFMpeg;
use App\Lib\File\FFmpegHelper\Filters\Video\ResizeFilter;
use App\Lib\File\FFmpegHelper\Format\Video\CuvId;

class TestCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'test';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filename = "/usr/local/var/www/testvideo/CRSHOPG0711144643031.mp4";
        //$filename = 'http://v.libraryplus.bjadks.com/target/video/201909/1080P/20190920183109_3281.mp4';

        $ffmpeg = FFMpeg::create(array(
            'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg', // ffmpeg path
            'ffprobe.binaries' => '/usr/local/bin/ffprobe',// ffprobe path
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 24,   // The number of threads that FFMpeg should use
        ));


        $video = $ffmpeg->open($filename);
        $format = new CuvId('videotoolbox','h264_videotoolbox','');

        $format->on('progress', function ($video, $format, $percentage){
            echo $percentage.' ';
        });

        $video->filters()
            ->resize(new Dimension(1280, 720),ResizeFilter::RESIZEMODE_INSET, true)
            ->synchronize();

        $format->setKiloBitrate(1500);
        $res = $video->save($format, 'a.mp4');
    }

}
