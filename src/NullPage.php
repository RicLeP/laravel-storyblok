<?php


namespace Riclep\Storyblok;


class NullPage
{

	public $_componentPath = ['page'];
	public $_meta = [];


	public function page() {
		return $this;
	}

}