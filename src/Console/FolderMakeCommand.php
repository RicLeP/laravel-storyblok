<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class FolderMakeCommand extends GeneratorCommand
{
	protected $name  = 'ls:folder';

	protected $description = 'Create a new folder class';

	protected $type = 'Folder';

	protected function getStub(): string
	{
		return file_exists(resource_path('stubs/storyblok/folder.stub')) ? resource_path('stubs/storyblok/folder.stub') : __DIR__ . '/stubs/folder.stub';
	}

	protected function getDefaultNamespace($rootNamespace): string
	{
		return $rootNamespace . '\Storyblok\Folders';
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
