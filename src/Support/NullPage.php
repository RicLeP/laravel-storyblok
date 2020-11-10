<?php


namespace Riclep\Storyblok\Support;


class NullPage
{

	public $_componentPath = ['page'];
	public $_meta = [];


	public function page() {
		return $this;
	}

}