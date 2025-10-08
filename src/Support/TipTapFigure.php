<?php

namespace Riclep\Storyblok\Support;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class TipTapFigure extends Node
{
    public static $name = 'image';

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [],
        ];
    }

    public function addAttributes()
    {
        return [
            'src' => [],
            'alt' => [],
            'title' => [],
        ];
    }

    public function renderText($node)
    {
        return $node->attrs->title;
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        $imageAttributes = HTML::mergeAttributes(
            $this->options['HTMLAttributes'],
            $HTMLAttributes
        );

        $src = $node->attrs->src ?? '';
        $width = null;
        $height = null;

        if (config('storyblok.tiptap.figure-transformation') && array_key_exists('src', $imageAttributes)) {
            $width = config('storyblok.tiptap.figure-transformation.width', 0);
            $height = config('storyblok.tiptap.figure-transformation.height', 0);

            $url = $src . '/m/' . $width . 'x' . $height . '/' . config('storyblok.tiptap.figure-transformation.filters', '');

            $imageAttributes['src'] = $url;
        } else {
            // Pattern: /f/{space_id}/{width}x{height}/{hash}/{filename}
            if (preg_match('/\/(\d+)x(\d+)\/[a-f0-9]+\//', $src, $matches)) {
                $width = $matches[1];
                $height = $matches[2];
            }
        }

        if ($width && $height) {
            $imageAttributes['width'] = $width;
            $imageAttributes['height'] = $height;
        }

        $figureChildren = [
            'img',
            $imageAttributes,
            0
        ];

        if ($node->attrs->title) {
            $figureChildren[] = [
                'figcaption',
                0
            ];
        }

        return [
            'figure',
            $figureChildren
        ];
    }
}
