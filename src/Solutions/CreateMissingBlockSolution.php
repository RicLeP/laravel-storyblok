<?php

namespace Riclep\Storyblok\Solutions;

use Facade\IgnitionContracts\RunnableSolution;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class CreateMissingBlockSolution implements RunnableSolution
{
	protected $data;

	public function __construct($data = null)
	{
		$this->data = $data;
	}

	public function getSolutionTitle(): string
	{
		if (get_class($this->data) === 'App\Storyblok\Block') {
			return 'Create a view or custom Block class';
		} else {
			return 'Create a view or implement view logic';
		}
	}

	public function getSolutionDescription(): string
	{
		if (get_class($this->data) === 'App\Storyblok\Block') {
			return 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or a create Block class called `App\Storyblok\Blocks\\' . Str::studly($this->data->meta()['component']) . '` and override the `views()` method implmenting your own view finding logic.';
		} else {
			return 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or override the `views()` method in `App\Storyblok\Blocks\\' . Str::studly($this->data->meta()['component']) . '` and implement your own view finding logic.';
		}
	}

	public function getDocumentationLinks(): array
	{
		return [
			'Laravel Storyblok docs' => 'https://ls.sirric.co.uk/docs/',
		];
	}

	public function getSolutionActionDescription(): string
	{
		return 'We can try to solve this exception by running a little code';
	}

	public function getRunButtonText(): string
	{
		return 'Create ' . Str::studly($this->data->meta()['component']) . ' Block class';
	}

	public function run(array $parameters = [])
	{
		Artisan::call('ls:block', $parameters);
	}

	public function getRunParameters(): array
	{
		return [
			'name' => Str::studly($this->data->meta()['component']),
		];
	}
}