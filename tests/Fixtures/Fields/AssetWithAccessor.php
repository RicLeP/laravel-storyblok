<?php


namespace Riclep\Storyblok\Tests\Fixtures\Fields;


use Riclep\Storyblok\Fields\Asset;

class AssetWithAccessor extends Asset
{
	public function getFilenameBackwardsAttribute() {
		return strrev($this->filename);
	}
}