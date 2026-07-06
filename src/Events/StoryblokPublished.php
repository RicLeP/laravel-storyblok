<?php

namespace Riclep\Storyblok\Events;

use Illuminate\Foundation\Events\Dispatchable;

class StoryblokPublished implements PublishingEvent
{
    use Dispatchable;

    /**
     * Receives the webhook JSON, see:
     * https://www.storyblok.com/docs/guide/in-depth/webhooks#available-webhooks
     */
    public function __construct(public $webhookPayload) {}
}
