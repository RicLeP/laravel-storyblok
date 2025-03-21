<?php

namespace Riclep\Storyblok\Http\Controllers;

use Illuminate\Http\Request;
use Riclep\Storyblok\Events\StoryblokPublished;
use Riclep\Storyblok\Events\StoryblokUnpublished;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WebhookController
{
    /**
     * Clears the cache when changing the publish state of a Story
     *
     * @param Request $request
     * @return bool[]
     */
    public function publish(Request $request): array
    {
        // Get the webhook secret and request signature once
        $webhookSecret = config("storyblok.webhook_secret");
        $requestSignature = $request->header("webhook-signature");

        // Check if the header is neccessary and if it is set
        if (!empty($webhookSecret) && $requestSignature === null) {
            throw new BadRequestHttpException("Header not set");
        }

        // Skip signature check if no secret configured
        if (!empty($webhookSecret)) {
            $expectedSignature = hash_hmac("sha1", $request->getContent(), $webhookSecret);

            if ($requestSignature !== $expectedSignature) {
                throw new BadRequestHttpException("Signature has invalid format");
            }
        }

        if ($request->all()["action"] === "published") {
            StoryblokPublished::dispatch($request->all());
        } elseif ($request->all()["action"] === "unpublished" || $request->all()["action"] === "deleted") {
            StoryblokUnpublished::dispatch($request->all());
        }

        return ["success" => true];
    }
}
