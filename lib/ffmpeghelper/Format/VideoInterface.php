<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib\File\FFmpegHelper\Format;

interface VideoInterface extends AudioInterface
{
    /**
     * Gets the kiloBitrate value.
     *
     * @return integer
     */
    public function getKiloBitrate();

    /**
     * Returns the modulus used by the Resizable video.
     *
     * This used to calculate the target dimensions while maintaining the best
     * aspect ratio.
     *
     * @see http://www.undeadborn.net/tools/rescalculator.php
     *
     * @return integer
     */
    public function getModulus();

    /**
     * Returns the video codec.
     *
     * @return string
     */
    public function getVideoCodec();

    /**
     * Returns true if the current format supports B-Frames.
     *
     * @see https://wikipedia.org/wiki/Video_compression_picture_types
     *
     * @return Boolean
     */
    public function supportBFrames();

    /**
     * Returns the list of available video codecs for this format.
     *
     * @return array
     */
    public function getAvailableVideoCodecs();

    /**
     * Returns the list of available video codecs for this format.
     *
     * @return array
     */
    public function getAdditionalParameters();

    /**
     * @return mixed 硬件加速编码
     */
    public function getVideoHardEncode();

    /**
     * @return mixed 硬件加速解码
     */
    public function getVideoHardDecode();

    /**
     * @return mixed 可获得的硬件编码
     */
    public function getAvailableVideoHardCodes();

    /**
     * @return mixed 硬件加速选项
     */
    public function getAvailableVideoHardware();
}
