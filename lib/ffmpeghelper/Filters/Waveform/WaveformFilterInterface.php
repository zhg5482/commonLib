<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Strime <contact@strime.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Filters\Waveform;

use App\Lib\File\FFmpegHelper\Filters\FilterInterface;
use App\Lib\File\FFmpegHelper\Media\Waveform;

interface WaveformFilterInterface extends FilterInterface
{
    public function apply(Waveform $waveform);
}
