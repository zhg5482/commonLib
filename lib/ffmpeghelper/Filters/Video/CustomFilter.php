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

class CustomFilter implements VideoFilterInterface
{
    /** @var string */
    private $filter;
    /** @var integer */
    private $priority;

    /**
     * A custom filter, useful if you want to build complex filters
     *
     * @param string $filter
     * @param int    $priority
     */
    public function __construct($filter, $priority = 0)
    {
        $this->filter = $filter;
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
        $commands = array('-vf', $this->filter);

        return $commands;
    }
}
