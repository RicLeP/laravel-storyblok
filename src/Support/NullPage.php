<?php


namespace Riclep\Storyblok\Support;


class NullPage
{
	public array $_componentPath = ['page'];
	public array $_meta = [];


	public function page(): NullPage
	{
		return $this;
	}

}