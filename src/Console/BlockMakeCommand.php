<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\GeneratorCommand;

class BlockMakeCommand extends GeneratorCommand
{
	protected $signature  = 'ls:block';

	protected $description = 'Create a new block class';

	protected $type = 'Block';

	protected function getStub()
	{
		return __DIR__ . '/stubs/block.stub';
	}

	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace . '\Block';
	}

	public function handle()
	{
		parent::handle();

		$this->doOtherOperations();
	}

	protected function doOtherOperations()
	{
		// Get the fully qualified class name (FQN)
		$class = $this->qualifyClass($this->getNameInput());

		// get the destination path, based on the default namespace
		$path = $this->getPath($class);

		$content = file_get_contents($path);

		// Update the file content with additional data (regular expressions)

		file_put_contents($path, $content);
	}
}
