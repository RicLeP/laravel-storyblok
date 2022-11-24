<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;


use Riclep\Storyblok\RequestStory;
use Riclep\Storyblok\Tests\Fixtures\Block;

class Relation extends Block
{
	public array $_resolveRelations = [
		'single_option_story',
		'multi_options_stories'
	];

	public function getRelation(RequestStory $requestStory, $relation, $className = null): mixed
	{
		$response = json_decode(file_get_contents(__DIR__ . '/../' . $relation . '.json'), true)['story'];

		if (!$className) {
			$class = $this->getChildClassName('Block', $response['content']['component']);
		} else {
			$class = $className;
		}

		$relationClass = new $class($response['content'], $this);

		$relationClass->addMeta([
			'name' => $response['name'],
			'published_at' => $response['published_at'],
			'full_slug' => $response['full_slug'],
		]);

		return $relationClass;
	}
}