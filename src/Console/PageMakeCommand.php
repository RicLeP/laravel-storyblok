<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class PageMakeCommand extends GeneratorCommand
{
	protected $name  = 'ls:page';

	protected $description = 'Create a new page class';

	protected $type = 'Page';

	protected function getStub(): string
	{
		return file_exists(resource_path('stubs/storyblok/page.stub')) ? resource_path('stubs/storyblok/page.stub') : __DIR__ . '/stubs/page.stub';
	}

	protected function getDefaultNamespace($rootNamespace): string
	{
		return $rootNamespace . '\Storyblok\Pages';
	}

	public function handle(): void
	{
		parent::handle();

		$this->doOtherOperations();
	}

	protected function doOtherOperations(): void
	{
		$class = $this->qualifyClass($this->getNameInput());
		$path = $this->getPath($class);
		$content = file_get_contents($path);

		$content = str_replace('DummySlug', Str::kebab($this->getNameInput()), $content);

		file_put_contents($path, $content);
	}
}
