<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <dev.team@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Format;

use Evenement\EventEmitterInterface;
use App\Lib\File\FFmpegHelper\FFProbe;
use App\Lib\File\FFmpegHelper\Media\MediaTypeInterface;

interface ProgressableInterface extends EventEmitterInterface
{
    /**
     * Creates the progress listener.
     *
     * @param MediaTypeInterface $media
     * @param FFProbe            $ffprobe
     * @param Integer            $pass    The current pas snumber
     * @param Integer            $total   The total pass number
     * @param Integer            $duration   The new video duration
     *
     * @return array An array of listeners
     */
    public function createProgressListener(MediaTypeInterface $media, FFProbe $ffprobe, $pass, $total, $duration = 0);
}
