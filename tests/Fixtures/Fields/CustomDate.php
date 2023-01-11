<?php


namespace Riclep\Storyblok\Tests\Fixtures\Fields;


use Riclep\Storyblok\Fields\DateTime;

class CustomDate extends DateTime
{
	protected string $format = 'd/m/Y';
}