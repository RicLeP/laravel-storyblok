<?php

namespace Riclep\Storyblok\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\Solution;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Illuminate\Support\Str;
use Riclep\Storyblok\Solutions\CreateMissingBlockSolution;

class UnableToRenderException extends Exception implements ProvidesSolution
{

	public function __construct($message, protected $data)
	{
		parent::__construct($message);
	}

	/** @return  \Spatie\Ignition\Contracts\Solution */
	public function getSolution(): Solution
	{
		if (get_class($this->data) === 'App\Storyblok\Block') {
			return new CreateMissingBlockSolution($this->data);
		}

		if (count($this->data->_componentPath) === 1) {
			if (get_class($this->data) === 'App\Storyblok\Page') {
				$title = 'Create a view or custom Page class';
				$description = 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or a create Page class called `App\Storyblok\Pages\\' . Str::studly($this->data->block()->component()) . '` and override the `views()` method implementing your own view finding logic.';
			} else {
				$title = 'Create a view or implement view logic';
				$description = 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or override the `views()` method in `App\Storyblok\Pages\\' . Str::studly($this->data->block()->component()) . '` and implement your own view finding logic.';
			}
		} else {
			$title = 'Create a view or implement view logic';
			$description = 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or override the `views()` method in `App\Storyblok\Blocks\\' . Str::studly($this->data->meta()['component']) . '` and implement your own view finding logic.';
		}

		return BaseSolution::create($title)
			->setSolutionDescription($description)
			->setDocumentationLinks([
				'Laravel Storyblok docs' => 'https://ls.sirric.co.uk/docs/',
			]);
	}


}