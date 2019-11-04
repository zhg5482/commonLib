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

use App\Lib\File\FFmpegHelper\Coordinate\TimeCode;
use App\Lib\File\FFmpegHelper\Format\AudioInterface;
use App\Lib\File\FFmpegHelper\Media\Audio;

class AudioClipFilter implements AudioFilterInterface {

    /**
     * @var TimeCode
     */
    private $start;

    /**
     * @var TimeCode
     */
    private $duration;

    /**
     * @var int
     */
    private $priority;


    public function __construct(TimeCode $start, TimeCode $duration = null, $priority = 0) {
        $this->start = $start;
        $this->duration = $duration;
        $this->priority = $priority;
    }

    /**
     * @inheritDoc
     */
    public function getPriority() {
        return $this->priority;
    }

     /**
      * Returns the start position the audio is being cutted
      *
      * @return TimeCode
      */
     public function getStart() {
         return $this->start;
     }

     /**
      * Returns how long the audio is being cutted. Returns null when the duration is infinite,
      *
      * @return TimeCode|null
      */
     public function getDuration() {
         return $this->duration;
     }

     /**
      * @inheritDoc
      */
     public function apply(Audio $audio, AudioInterface $format) {
         $commands = array('-ss', (string) $this->start);

         if ($this->duration !== null) {
            $commands[] = '-t';
            $commands[] = (string) $this->duration;
         }

         $commands[] = '-acodec';
         $commands[] = 'copy';

         return $commands;
     }

}
