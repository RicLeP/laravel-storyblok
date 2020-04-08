<?php


namespace Riclep\Storyblok\Traits;

use Storyblok\RichtextRender\Resolver;

trait ConvertsRichtext
{
	protected $richtext = [];

	public function convertRichtext() {
		if ($this->richtext && count($this->richtext)) {
			$richtextResolver = new Resolver();

			foreach ($this->richtext as $richtextField) {
				if ($this->content->has($richtextField)) {
					$this->content[$richtextField . '_html'] = $richtextResolver->render($this->content[$richtextField]);
				}
			}
		}
	}
}