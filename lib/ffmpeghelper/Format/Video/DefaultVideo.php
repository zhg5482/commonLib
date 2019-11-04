<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Format\Video;

use App\Lib\File\FFmpegHelper\FFProbe;
use App\Lib\File\FFmpegHelper\Exception\InvalidArgumentException;
use App\Lib\File\FFmpegHelper\Format\Audio\DefaultAudio;
use App\Lib\File\FFmpegHelper\Format\VideoInterface;
use App\Lib\File\FFmpegHelper\Media\MediaTypeInterface;
use App\Lib\File\FFmpegHelper\Format\ProgressListener\VideoProgressListener;

/**
 * The abstract default Video format
 */
abstract class DefaultVideo extends DefaultAudio implements VideoInterface
{
    /** @var string */
    protected $videoCodec;

    /** @var Integer */
    protected $kiloBitrate = 1000;

    /** @var Integer */
    protected $modulus = 16;

    /** @var Array */
    protected $additionalParamaters;

    public $videoHardEncode;
    public $videoHardDecode;
    public $videoHardware;

    public function getVideoHardDecode()
    {
        return $this->videoHardDecode;
    }

    public function getVideoHardEncode()
    {
        return $this->videoHardEncode;
    }

    public function setVideoHardEnCode($videoHardEnCode)
    {
        if ( ! in_array($videoHardEnCode, $this->getAvailableVideoHardCodes())) {
            throw new InvalidArgumentException(sprintf(
                'Wrong videocodec value for %s, available formats are %s'
                , $videoHardEnCode, implode(', ', $this->getAvailableVideoHardCodes())
            ));
        }

        $this->videoHardEncode = $videoHardEnCode;

        return $this;
    }

    public function setVideoHardDeCode($videoHardDeCode)
    {
        if ( ! in_array($videoHardDeCode, $this->getAvailableVideoHardCodes())) {
            throw new InvalidArgumentException(sprintf(
                'Wrong videocodec value for %s, available formats are %s'
                , $videoHardDeCode, implode(', ', $this->getAvailableVideoHardCodes())
            ));
        }

        $this->videoHardDecode = $videoHardDeCode;

        return $this;
    }

    public function setVideoHardware($videoHardware)
    {
        if ( ! in_array($videoHardware, $this->getAvailableVideoHardware())) {
            throw new InvalidArgumentException(sprintf(
                'Wrong videocodec value for %s, available formats are %s'
                , $videoHardware, implode(', ', $this->getAvailableVideoHardware())
            ));
        }

        $this->videoHardware = $videoHardware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getKiloBitrate()
    {
        return $this->kiloBitrate;
    }

    /**
     * Sets the kiloBitrate value.
     *
     * @param  integer                  $kiloBitrate
     * @throws InvalidArgumentException
     */
    public function setKiloBitrate($kiloBitrate)
    {
        if ($kiloBitrate < 1) {
            throw new InvalidArgumentException('Wrong kiloBitrate value');
        }

        $this->kiloBitrate = (int) $kiloBitrate;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVideoCodec()
    {
        return $this->videoCodec;
    }

    /**
     * Sets the video codec, Should be in the available ones, otherwise an
     * exception is thrown.
     *
     * @param  string                   $videoCodec
     * @throws InvalidArgumentException
     */
    public function setVideoCodec($videoCodec)
    {
        if ( ! in_array($videoCodec, $this->getAvailableVideoCodecs())) {
            throw new InvalidArgumentException(sprintf(
                    'Wrong videocodec value for %s, available formats are %s'
                    , $videoCodec, implode(', ', $this->getAvailableVideoCodecs())
            ));
        }

        $this->videoCodec = $videoCodec;

        return $this;
    }

    /**
     * @return integer
     */
    public function getModulus()
    {
        return $this->modulus;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalParameters()
    {
        return $this->additionalParamaters;
    }

    /**
     * Sets additional parameters.
     *
     * @param  array                    $additionalParamaters
     * @throws InvalidArgumentException
     */
    public function setAdditionalParameters($additionalParamaters)
    {
        if (!is_array($additionalParamaters)) {
            throw new InvalidArgumentException('Wrong additionalParamaters value');
        }

        $this->additionalParamaters = $additionalParamaters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createProgressListener(MediaTypeInterface $media, FFProbe $ffprobe, $pass, $total, $duration = 0)
    {
        $format = $this;
        $listeners = array(new VideoProgressListener($ffprobe, $media->getPathfile(), $pass, $total, $duration));

        foreach ($listeners as $listener) {
            $listener->on('progress', function () use ($format, $media) {
               $format->emit('progress', array_merge(array($media, $format), func_get_args()));
            });
        }

        return $listeners;
    }

    /**
     * @return array|mixed
     */
    public function getAvailableVideoHardCodes()
    {
        return array('h264_cuvid', 'h264_nvenc', 'h264_videotoolbox','libx264','');
    }

    /**
     * @return array
     */
    public function getAvailableVideoHardware()
    {
        return array('cuvid', 'videotoolbox');
    }
}
