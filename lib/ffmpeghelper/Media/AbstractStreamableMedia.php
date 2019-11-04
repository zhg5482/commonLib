<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Media;

use App\Lib\File\FFmpegHelper\FFProbe\DataMapping\Format;
use App\Lib\File\FFmpegHelper\FFProbe\DataMapping\StreamCollection;

abstract class AbstractStreamableMedia extends AbstractMediaType
{
    private $streams;

    /**
     * @return StreamCollection
     */
    public function getStreams()
    {
        if (null === $this->streams) {
            $this->streams = $this->ffprobe->streams($this->pathfile);
        }

        return $this->streams;
    }

    /**
     * @return Format
     */
    public function getFormat()
    {
        return $this->ffprobe->format($this->pathfile);
    }
}
