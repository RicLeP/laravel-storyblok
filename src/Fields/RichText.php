<?php


namespace Riclep\Storyblok\Fields;


use Riclep\Storyblok\Field;
use Riclep\Storyblok\Traits\HasChildClasses;
use Storyblok\RichtextRender\Resolver;

class RichText extends Field
{
	use HasChildClasses;

	public function init() {
		$richtextResolver = new Resolver();

		$content = [];

		// Loop through all the nodes looking for a ‘blok’ nodes and convery them to
		// the correct Block Class. All other nodes are converted to HTML
		foreach ($this->content['content'] as $key => $node) {
			if ($node['type'] === 'blok' && isset($node['attrs']['body']) && is_array($node['attrs']['body'])){
				$class = $this->getChildClassName('Block', $node['attrs']['body'][0]['component']);
				$block = new $class($node['attrs']['body'][0], $this->block());

				$content[] = $block;
			} else {
				$content[] = $richtextResolver->render(["content" => [$node]]);
			}
		}

		$this->content = collect($content);
	}

	/**
	 * Converts the data to HTML when printed. If there is an inline Component
	 * it will use it’s render method.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$html = "";

		foreach ($this->content as $node) {
			if (is_string($node)) {
				$html .= $node;
			} else {
				$html .= $node->render();
			}
		}

		return $html;
	}
}