<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class BlockMakeCommand extends GeneratorCommand
{
	protected $name  = 'ls:block';

	protected $description = 'Create a new block class';

	protected $type = 'Block';

	protected function getStub()
	{
		return __DIR__ . '/stubs/block.stub';
	}

	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace . '\Storyblok\Blocks';
	}

	public function handle()
	{
		parent::handle();

		$this->doOtherOperations();

		if ($this->option('blade')) {
			$this->createBlade();
		}

		if ($this->option('scss')) {
			$this->createScss();
		}
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

	protected function createBlade() {
		// call API to get details on the Block and name comments of vars available

		$name = Str::kebab($this->getNameInput());

		$content = file_get_contents(__DIR__ . '/stubs/block.blade.stub');
		$content = str_replace('DummyClass', $name, $content);

		file_put_contents($this->viewPath('storyblok/components/') . $name . '.blade.php', $content);
	}

	protected function createScss() {
		$name = Str::kebab($this->getNameInput());

		$content = file_get_contents(__DIR__ . '/stubs/block.scss.stub');
		$content = str_replace('DummyClass', $name, $content);

		file_put_contents(resource_path('sass/blocks/') . '_' . $name . '.scss', $content);

		// TODO - update app.scss
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['blade', 'b', InputOption::VALUE_NONE, 'Create stub Blade view'],
			['scss', 's', InputOption::VALUE_NONE, 'Create stub SCSS view'],
		];
	}
}
