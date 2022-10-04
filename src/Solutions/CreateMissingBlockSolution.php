<?php

namespace Riclep\Storyblok\Solutions;

use Spatie\Ignition\Contracts\RunnableSolution;
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
		}

		return 'Create a view or implement view logic';
	}

	public function getSolutionDescription(): string
	{
		if (get_class($this->data) === 'App\Storyblok\Block') {
			return 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or a create Block class called `App\Storyblok\Blocks\\' . Str::studly($this->data->meta()['component']) . '` and override the `views()` method implementing your own view finding logic. You can also scaffold all your views using `artisan ls:stub-views`.';
		}

		return 'Create one of the following views: `[' . implode(', ', $this->data->views()) . ']` or override the `views()` method in `App\Storyblok\Blocks\\' . Str::studly($this->data->meta()['component']) . '` and implement your own view finding logic. You can also scaffold all your views using `artisan ls:stub-views`.';
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

	public function run(array $parameters = []): void
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