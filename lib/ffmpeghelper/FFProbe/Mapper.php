<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\FFProbe;

use App\Lib\File\FFmpegHelper\FFProbe;
use App\Lib\File\FFmpegHelper\FFProbe\DataMapping\Format;
use App\Lib\File\FFmpegHelper\FFProbe\DataMapping\StreamCollection;
use App\Lib\File\FFmpegHelper\FFProbe\DataMapping\Stream;
use App\Lib\File\FFmpegHelper\Exception\InvalidArgumentException;

class Mapper implements MapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function map($type, $data)
    {
        switch ($type) {
            case FFProbe::TYPE_FORMAT:
                return $this->mapFormat($data);
            case FFProbe::TYPE_STREAMS:
                return $this->mapStreams($data);
            default:
                throw new InvalidArgumentException(sprintf(
                    'Invalid type `%s`.', $type
                ));
        }
    }

    private function mapFormat($data)
    {
        return new Format($data['format']);
    }

    private function mapStreams($data)
    {
        $streams = new StreamCollection();

        foreach ($data['streams'] as $properties) {
            $streams->add(new Stream($properties));
        }

        return $streams;
    }
}
