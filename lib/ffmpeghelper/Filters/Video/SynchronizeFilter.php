<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <dev.team@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Filters\Video;

use App\Lib\File\FFmpegHelper\Format\VideoInterface;
use App\Lib\File\FFmpegHelper\Media\Video;

/**
 * Synchronizes audio and video in case of desynchronized movies.
 */
class SynchronizeFilter implements VideoFilterInterface
{
    private $priority;

    public function __construct($priority = 12)
    {
        $this->priority = $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Video $video, VideoInterface $format)
    {
        return array('-async', '1', '-metadata:s:v:0', 'start_time=0');
    }
}
