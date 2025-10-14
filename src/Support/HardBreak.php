<?php

namespace Riclep\Storyblok\Support;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class HardBreak extends Node
{
    public static $name = 'hard_break';

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [],
        ];
    }

    public function parseHTML()
    {
        return [
            [
                'tag' => 'br',
            ],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['br', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes)];
    }
}
