<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Strime <contact@strime.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Filters\Gif;

use App\Lib\File\FFmpegHelper\Filters\FilterInterface;
use App\Lib\File\FFmpegHelper\Media\Gif;

interface GifFilterInterface extends FilterInterface
{
    public function apply(Gif $gif);
}
