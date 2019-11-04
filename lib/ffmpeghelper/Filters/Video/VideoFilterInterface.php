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

use App\Lib\File\FFmpegHelper\Filters\FilterInterface;
use App\Lib\File\FFmpegHelper\Format\VideoInterface;
use App\Lib\File\FFmpegHelper\Media\Video;

interface VideoFilterInterface extends FilterInterface
{
    /**
     * Applies the filter on the the Video media given an format.
     *
     * @param Video          $video
     * @param VideoInterface $format
     *
     * @return array An array of arguments
     */
    public function apply(Video $video, VideoInterface $format);
}
