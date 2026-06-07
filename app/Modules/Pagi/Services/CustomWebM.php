<?php

namespace App\Modules\Pagi\Services;

use FFMpeg\Format\Video\DefaultVideo;

class CustomWebM extends DefaultVideo
{
    /**
     * Create a new CustomWebM format instance with libopus audio codec.
     */
    public function __construct()
    {
        $this
            ->setVideoCodec('libvpx')
            ->setAudioCodec('libopus');
    }

    /**
     * Get the available audio codecs for this format.
     *
     * @return array<int, string>
     */
    public function getAvailableAudioCodecs(): array
    {
        return ['libvorbis', 'libopus', 'copy'];
    }

    /**
     * Get the available video codecs for this format.
     *
     * @return array<int, string>
     */
    public function getAvailableVideoCodecs(): array
    {
        return ['libvpx', 'libvpx-vp9'];
    }

    /**
     * Check if the format supports B-frames.
     *
     * @return bool
     */
    public function supportBFrames(): bool
    {
        return false;
    }
}
