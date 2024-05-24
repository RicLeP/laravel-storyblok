<?php

namespace Riclep\Storyblok\Support\ImageTransformers;

use Illuminate\Support\Str;

class StoryblokSvg extends BaseTransformer
{
    public function buildUrl(): string {
        return $this->assetDomain();
    }

    protected function extractMetaDetails(): void
    {
        $path = $this->image->content()['filename'];

        preg_match_all('/(?<width>\d+)x(?<height>\d+).+\.(?<extension>[a-z]{3,4})/mi', $path, $dimensions, PREG_SET_ORDER, 0);

        $this->meta = [
            'height' => $dimensions[0]['height'],
            'width' => $dimensions[0]['width'],
            'extension' => 'svg',
            'mime' => 'image/svg+xml',
        ];
    }

    public function resize(int $width, int $height = null): static
    {
        return $this;
    }

    public function fitIn(int $width = 0, int $height = 0, string $fill = 'transparent'): static
    {
        return $this;
    }

    public function format(string $format, int $quality = null): static
    {
        return $this;
    }

    /**
     * Sets the asset domain
     *
     * @param $options
     * @return string
     */
    protected function assetDomain($options = null): string
    {
        $resource = str_replace(config('storyblok.asset_domain'), config('storyblok.image_service_domain'), $this->image->content()['filename']);

        return $resource;
    }
}
