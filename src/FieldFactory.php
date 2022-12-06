<?php

namespace Riclep\Storyblok;

use Illuminate\Support\Str;
use Riclep\Storyblok\Fields\Asset;
use Riclep\Storyblok\Fields\Image;
use Riclep\Storyblok\Fields\MultiAsset;
use Riclep\Storyblok\Fields\RichText;
use Riclep\Storyblok\Fields\Table;

class FieldFactory
{
	/**
	 * Works out what class should be used for the given block’s content
	 *
	 * @param $block
	 * @param $field
	 * @param $key
	 * @return \Illuminate\Support\Collection|mixed|Asset|Image|MultiAsset|RichText|Table
	 */
	public function build($block, $field, $key): mixed
	{
		$isClassField = $this->classField($block, $field, $key);

		if ($isClassField) {
			return $isClassField;
		}

		// single item relations
		if (Str::isUuid($field) && ($block->_autoResolveRelations || in_array($key, $block->_resolveRelations, true) || array_key_exists($key, $block->_resolveRelations))) {

			if (array_key_exists($key, $block->_resolveRelations)) {
				return $block->getRelation(new RequestStory(), $field, $block->_resolveRelations[$key]);
			}

			return $block->getRelation(new RequestStory(), $field);
		}

		// complex fields
		if (is_array($field) && !empty($field)) {
			return $this->arrayField($block, $field, $key);
		}

		// legacy and string image fields
		if ($this->isStringImageField($field)) {
			return new Image($field, $block);
		}

		// strings or anything else - do nothing
		return $field;
	}

	/**
	 * @param $block
	 * @param $field
	 * @param $key
	 * @return mixed
	 */
	protected function classField($block, $field, $key): mixed
	{
		// does the Block assign any $_casts? This is key (field) => value (class)
		if (array_key_exists($key, $block->getCasts())) {
			$casts = $block->getCasts();
			return new $casts[$key]($field, $block);
		}

		// find Fields specific to this Block matching: BlockNameFieldName
		if ($class = $block->getChildClassName('Field', $block->component() . '_' . $key)) {
			return new $class($field, $block);
		}

		// auto-match Field classes
		if ($class = $block->getChildClassName('Field', $key)) {
			return new $class($field, $block);
		}

		return false;
	}

	/**
	 * If given an array field we need more processing to determine the class
	 *
	 * @param $block
	 * @param $field
	 * @param $key
	 * @return \Illuminate\Support\Collection|mixed|Asset|Image|MultiAsset|RichText|Table
	 */
	protected function arrayField($block, $field, $key): mixed
	{
		// match link fields
		if (array_key_exists('linktype', $field)) {
			$class = 'Riclep\Storyblok\Fields\\' . Str::studly($field['linktype']) . 'Link';

			return new $class($field, $block);
		}

		// match rich-text fields
		if (array_key_exists('type', $field) && $field['type'] === 'doc') {
			return new RichText($field, $block);
		}

		// match asset fields - detecting raster images
		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'asset') {
			// legacy and string image fields
			if($this->isStringImageField($field['filename'])) {
				return new Image($field, $block);
			}

			return new Asset($field, $block);
		}

		// match table fields
		if (array_key_exists('fieldtype', $field) && $field['fieldtype'] === 'table') {
			return new Table($field, $block);
		}

		if (array_key_exists(0, $field)) {
			return $this->relationField($block, $field, $key);
		}

		// just return the array
		return $field;
	}

	protected function relationField($block, $field, $key) {
		// it’s an array of relations - request them if we’re auto or manual resolving
		if (Str::isUuid($field[0])) {
			if ($block->_autoResolveRelations || array_key_exists($key, $block->_resolveRelations) || in_array($key, $block->_resolveRelations, true)) {

				// they’re passing a custom class
				if (array_key_exists($key, $block->_resolveRelations)) {
					$relations = collect($field)->transform(fn($relation) => $block->getRelation(new RequestStory(), $relation, $block->_resolveRelations[$key]));
				} else {
					$relations = collect($field)->transform(fn($relation) => $block->getRelation(new RequestStory(), $relation));
				}

				if ($block->_filterRelations) {
					$relations = $relations->filter();
				}

				return $relations;
			}
		}

		// has child items - single option, multi option and Blocks fields
		if (is_array($field[0])) {
			// resolved relationships - entire story is returned, we just want the content and a few meta items
			if (array_key_exists('content', $field[0])) {
				return collect($field)->transform(function ($relation) use ($block) {
					$class = $block->getChildClassName('Block', $relation['content']['component']);
					$relationClass = new $class($relation['content'], $block);

					$relationClass->addMeta([
						'name' => $relation['name'],
						'published_at' => $relation['published_at'],
						'full_slug' => $relation['full_slug'],
					]);

					return $relationClass;
				});
			}

			// this field holds blocks!
			if (array_key_exists('component', $field[0])) {
				return collect($field)->transform(function ($childBlock) use ($block) {
					$class = $block->getChildClassName('Block', $childBlock['component']);

					return new $class($childBlock, $block);
				});
			}

			// multi assets
			if (array_key_exists('filename', $field[0])) {
				return new MultiAsset($field, $block);
			}
		}

		return $field;
	}

	/**
	 * Check if given string is a string image field
	 *
	 * @param  $filename
	 * @return boolean
	 */
	public function isStringImageField($filename): bool
	{
		$allowed_extentions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

		return is_string($filename) && Str::of($filename)->lower()->endsWith($allowed_extentions);
	}
}