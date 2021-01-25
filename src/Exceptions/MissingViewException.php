<?php

namespace Riclep\Storyblok\Exceptions;

use Exception;
use Facade\IgnitionContracts\Solution;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Illuminate\Support\Str;

class MissingViewException extends Exception implements ProvidesSolution
{
	protected $data;


	public function __construct($message, $data)
	{
		parent::__construct($message);

		$this->data = $data;
	}

	/** @return  \Facade\IgnitionContracts\Solution */
	public function getSolution(): Solution
	{
		if (count($this->data->_componentPath) === 1) {
			if (get_class($this->data) === 'App\Storyblok\Page') {
				$title = 'Create a view or custom Page class';
				$description = 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or a create Page class called `App\Storyblok\Pages\\' . Str::studly($this->data->block()->component()) . '` and override the `views()` method implmenting your own view finding logic.';
			} else {
				$title = 'Create a view or implement view logic';
				$description = 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or override the `views()` method in `App\Storyblok\Pages\\' . Str::studly($this->data->block()->component()) . '` and implement your own view finding logic.';
			}
		} else {
			if (get_class($this->data) === 'App\Storyblok\Block') {
				$title = 'Create a view or custom Page class';
				$description = 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or a create Block class called `App\Storyblok\Blocks\\' . Str::studly($this->data->meta()['component']) . '` and override the `views()` method implmenting your own view finding logic.';
			} else {
				$title = 'Create a view or implement view logic';
				$description = 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or override the `views()` method in `App\Storyblok\Blocks\\' . Str::studly($this->data->meta()['component']) . '` and implement your own view finding logic.';
			}
		}

		return BaseSolution::create($title = '')
			->setSolutionDescription($description)
			->setDocumentationLinks([
				'Laravel Storyblok docs' => 'https://ls.sirric.co.uk/docs/',
			]);
	}


}