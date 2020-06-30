<?php


namespace Riclep\Storyblok\Traits;

use Storyblok\RichtextRender\Resolver;

trait ConvertsRichtext
{
	protected $richtext = [];

	/**
	 * Takes all the fields in $richtext and applies the Storyblok
	 * richtext transformer to them. Returns a duplicate field
	 * suffixed with _html
	 */
	public function convertRichtext() {
		if (!empty($this->richtext)) {
			$richtextResolver = new Resolver();

			foreach ($this->richtext as $richtextField) {
				if ($this->content->has($richtextField)) {
					$this->content[$richtextField . '_html'] = $richtextResolver->render($this->content[$richtextField]);
				}
			}
		}
	}
}