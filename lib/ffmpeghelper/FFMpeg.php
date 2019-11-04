<?php

namespace App\Lib\File\FFmpegHelper;

use Alchemy\BinaryDriver\ConfigurationInterface;
use App\Lib\File\FFmpegHelper\Driver\FFMpegDriver;
use App\Lib\File\FFmpegHelper\Exception\InvalidArgumentException;
use App\Lib\File\FFmpegHelper\Exception\RuntimeException;
use App\Lib\File\FFmpegHelper\Media\Audio;
use App\Lib\File\FFmpegHelper\Media\Video;
use Psr\Log\LoggerInterface;

class FFMpeg
{
    /** @var FFMpegDriver */
    private $driver;
    /** @var FFProbe */
    private $ffprobe;

    public function __construct(FFMpegDriver $ffmpeg, FFProbe $ffprobe)
    {
        $this->driver = $ffmpeg;
        $this->ffprobe = $ffprobe;
    }

    /**
     * Sets FFProbe.
     *
     * @param FFProbe
     *
     * @return FFMpeg
     */
    public function setFFProbe(FFProbe $ffprobe)
    {
        $this->ffprobe = $ffprobe;

        return $this;
    }

    /**
     * Gets FFProbe.
     *
     * @return FFProbe
     */
    public function getFFProbe()
    {
        return $this->ffprobe;
    }

    /**
     * Sets the ffmpeg driver.
     *
     * @return FFMpeg
     */
    public function setFFMpegDriver(FFMpegDriver $ffmpeg)
    {
        $this->driver = $ffmpeg;

        return $this;
    }

    /**
     * Gets the ffmpeg driver.
     *
     * @return FFMpegDriver
     */
    public function getFFMpegDriver()
    {
        return $this->driver;
    }

    /**
     * Opens a file in order to be processed.
     *
     * @param string $pathfile A pathfile
     *
     * @return Audio|Video
     *
     * @throws InvalidArgumentException
     */
    public function open($pathfile)
    {
        if (null === $streams = $this->ffprobe->streams($pathfile)) {
            throw new RuntimeException(sprintf('Unable to probe "%s".', $pathfile));
        }

        if (0 < count($streams->videos())) {
            return new Video($pathfile, $this->driver, $this->ffprobe);
        } elseif (0 < count($streams->audios())) {
            return new Audio($pathfile, $this->driver, $this->ffprobe);
        }

        throw new InvalidArgumentException('Unable to detect file format, only audio and video supported');
    }

    /**
     * Creates a new FFMpeg instance.
     *
     * @param array|ConfigurationInterface $configuration
     * @param LoggerInterface              $logger
     * @param FFProbe                      $probe
     *
     * @return FFMpeg
     */
    public static function create($configuration = array(), LoggerInterface $logger = null, FFProbe $probe = null)
    {
        if (null === $probe) {
            $probe = FFProbe::create($configuration, $logger, null);
        }

        return new static(FFMpegDriver::create($logger, $configuration), $probe);
    }
}
