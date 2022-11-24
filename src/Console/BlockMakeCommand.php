<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class BlockMakeCommand extends GeneratorCommand
{
	protected $name  = 'ls:block';

	protected $description = 'Create a new block class';

	protected $type = 'Block';

	protected function getStub(): string
	{
		return file_exists(resource_path('stubs/storyblok/block.stub')) ? resource_path('stubs/storyblok/block.stub') : __DIR__ . '/stubs/block.stub';
	}

	protected function getDefaultNamespace($rootNamespace): string
	{
		return $rootNamespace . '\Storyblok\Blocks';
	}

	public function handle(): void
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

	protected function doOtherOperations(): void
	{
		$class = $this->qualifyClass($this->getNameInput());
		$path = $this->getPath($class);
		$content = file_get_contents($path);

		file_put_contents($path, $content);

		$this->getComponentFields($this->getNameInput());
	}

	protected function createBlade(): void
	{
		$name = Str::kebab($this->getNameInput());
        $path = $this->viewPath( str_replace( '.' , '/' , config('storyblok.view_path') . 'blocks.' ) );
		$stub = file_exists(resource_path('stubs/storyblok/block.blade.stub')) ? resource_path('stubs/storyblok/block.blade.stub') : __DIR__ . '/stubs/block.blade.stub';

		if (!file_exists($path . $name . '.blade.php')) {
			$content = file_get_contents($stub);

			$find = ['DummyClass', 'DummyCssClass'];
			$replace = [$this->getNameInput(), $name];

			$content = str_replace($find, $replace, $content);

			if (!$this->files->exists($path)) {
				$this->files->makeDirectory($path);
			}

			file_put_contents($path . $name . '.blade.php', $content);
			$this->info('Blade created successfully.');
		} else {
			$this->error('Blade already exists!');
		}
	}

	protected function createScss(): void
	{
		$name = Str::kebab($this->getNameInput());
		$path = resource_path('sass/blocks/');
		$stub = file_exists(resource_path('stubs/storyblok/block.scss.stub')) ? resource_path('stubs/storyblok/block.scss.stub') : __DIR__ . '/stubs/block.scss.stub';

		if (!file_exists($path . '_' . $name . '.scss')) {
			$content = file_get_contents($stub);
			$content = str_replace('DummyClass', $name, $content);

			if (!$this->files->exists($path)) {
				$this->files->makeDirectory($path);
			}

			file_put_contents($path . '_' . $name . '.scss', $content);
			$this->info('SCSS created successfully.');
		} else {
			$this->error('SCSS already exists!');
		}

		$appContent = file_get_contents(resource_path('sass/app.scss'));

		preg_match_all("/^@import \"blocks(.*)\r$/m", $appContent, $matches);

		$files = $matches[0];
		$files[] = '@import "blocks/' . $name . '";';
		asort($files);

		$allFilesString = implode("\n", $files);

		$allButFirstFile = $files;

		array_shift($allButFirstFile);

		$appContent = str_replace($allButFirstFile, '', $appContent);

		// clean up all the empty lines left over from the replacement
		$appContent = preg_replace("/\n\n+/s","\n",$appContent);

		$appContent = str_replace($files[0], $allFilesString, $appContent);

		file_put_contents(resource_path('sass/app.scss'), $appContent);
	}

	protected function getComponentFields($name): void
	{
		if (config('storyblok.oauth_token')) {
			$this->call('ls:sync', [
				'component' => Str::studly($name),
			]);
		}
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions(): array
	{
		return [
			['blade', 'b', InputOption::VALUE_NONE, 'Create stub Blade view'],
			['scss', 's', InputOption::VALUE_NONE, 'Create stub SCSS view'],
		];
	}
}
