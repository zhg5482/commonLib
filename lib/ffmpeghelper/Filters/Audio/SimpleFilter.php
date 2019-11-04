<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Filters\Audio;

use App\Lib\File\FFmpegHelper\Media\Audio;
use App\Lib\File\FFmpegHelper\Format\AudioInterface;

class SimpleFilter implements AudioFilterInterface
{
    private $params;
    private $priority;

    public function __construct(array $params, $priority = 0)
    {
        $this->params = $params;
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
    public function apply(Audio $audio, AudioInterface $format)
    {
        return $this->params;
    }
}
