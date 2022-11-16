<?php


namespace Riclep\Storyblok\Tests\Fixtures\Blocks;

class RelationCustom extends Relation
{
	public $_resolveRelations = [
		'single_option_story' => CustomTwo::class,
		'multi_options_stories' => CustomTwo::class,
	];
}