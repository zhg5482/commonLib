<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Coordinate;

use App\Lib\File\FFmpegHelper\Exception\InvalidArgumentException;

class FrameRate
{
    private $value;

    public function __construct($value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Invalid frame rate, must be positive value.');
        }

        $this->value = $value;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}
