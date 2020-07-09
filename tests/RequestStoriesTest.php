<?php


namespace Riclep\Storyblok\Tests;


use Riclep\Storyblok\RequestStory;

class RequestStoriesTest extends TestCase
{
	/** @tttest */
	public function can_request_stories()
	{
		$requester = new RequestStory();
		$response = $requester->get('dfc9b1ec-3141-4775-980c-e9285c350601');

		dd($response);
	}
}