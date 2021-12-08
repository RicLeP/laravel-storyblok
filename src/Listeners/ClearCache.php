<?php

namespace Riclep\Storyblok\Listeners;

use Illuminate\Support\Facades\Cache;
use Riclep\Storyblok\Events\PublishingEvent;

class ClearCache
{
	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param PublishingEvent $event
	 * @return void
	 */
	public function handle(PublishingEvent $event)
	{
		if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
			Cache::tags('storyblok')->flush();
		} else {
			Cache::flush();
		}
	}
}