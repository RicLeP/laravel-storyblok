<?php


namespace Riclep\Storyblok;


class Storyblok
{
	public function read() {
		$request = new RequestStory();
		return $request->get();
	}
}