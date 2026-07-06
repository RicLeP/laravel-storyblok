<?php

namespace Riclep\Storyblok\Support;

use ArrayAccess;

class ImageTransformation implements ArrayAccess
{
    public function __construct(protected array $transformation) {}

    public function offsetExists($offset): bool
    {
        return isset($this->$transformation[$offset]);
    }

    /**
     * @return mixed|null
     */
    public function offsetGet($offset): mixed
    {
        return $this->transformation[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->transformation[] = $value;
        } else {
            $this->transformation[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->transformation[$offset]);
    }

    /**
     * Allows direct access to the Image Transformer object and it’s __toString
     */
    public function __toString(): string
    {
        return (string) $this->transformation['src'];
    }
}
