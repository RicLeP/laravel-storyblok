<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\RequestStory;
use Riclep\Storyblok\Tests\Fixtures\Block;

class Relation extends Block
{
	public $_resolveRelations = [
		'single_option_story',
		'multi_options_stories'
	];

	public function getRelation(RequestStory $request, $relation) {
		$response = json_decode(file_get_contents(__DIR__ . '/../' . $relation . '.json'), true)['story'];

		$class = $this->getChildClassName('Block', $response['content']['component']);
		$relationClass = new $class($response['content'], $this);

		$relationClass->addMeta([
			'name' => $response['name'],
			'published_at' => $response['published_at'],
			'full_slug' => $response['full_slug'],
		]);

		return $relationClass;
	}
}