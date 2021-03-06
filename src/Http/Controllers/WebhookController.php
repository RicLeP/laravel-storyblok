<?php

namespace Riclep\Storyblok\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WebhookController
{
	/**
	 * Clears the cache when changing the publish state of a Story
	 *
	 * @param Request $request
	 * @return bool[]
	 */
	public function publish(Request $request)
	{
		if ($request->headers->get('webhook-signature') === null) {
			throw new BadRequestHttpException('Header not set');
		}

		$signature = hash_hmac('sha1', $request->getContent(), config('storyblok.webhook_secret'));

		if ($request->header('webhook-signature') === $signature) {
			if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
				Cache::tags('storyblok')->flush();
			} else {
				Cache::flush();
			}

			return ['success' => true];
		}

		throw new BadRequestHttpException('Signature has invalid format');
	}
}
