<?php

namespace Riclep\Storyblok;

use Riclep\Storyblok\Traits\HasChildClasses;

class Storyblok
{
    use HasChildClasses;

    /**
     * Reads the requested story from the API
     */
    public function read(string $slug, ?array $resolveRelations = null, ?string $language = null, ?string $fallbackLanguage = null): Page
    {
        $storyblokRequest = new RequestStory;
        if ($resolveRelations) {
            $storyblokRequest->resolveRelations($resolveRelations);
        }

        if ($language) {
            $storyblokRequest->language($language, $fallbackLanguage);
        }

        $response = $storyblokRequest->get($slug);

        $class = $this->getChildClassName('Page', $response['content']['component']);

        return new $class($response);
    }

    public function setData($data): mixed
    {
        $response = $data;

        $class = $this->getChildClassName('Page', $response['content']['component']);

        return new $class($response);
    }
}
