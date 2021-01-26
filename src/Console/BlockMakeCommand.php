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
		$class = $this->qualifyClass($this->getNameInput());
		$path = $this->getPath($class);
		$content = file_get_contents($path);

		$content = str_replace('DummyPhpDoc', $this->getComponentFields(Str::kebab($this->getNameInput())), $content);

		file_put_contents($path, $content);
	}

	protected function createBlade() {
		// call API to get details on the Block and name comments of vars available

		$name = Str::kebab($this->getNameInput());
		$path = $this->viewPath('storyblok/blocks/');

		if (!file_exists($path . $name . '.blade.php')) {
			$content = file_get_contents(__DIR__ . '/stubs/block.blade.stub');

			$find = ['DummyClass', 'DummyCssClass'];
			$replace = [$this->getNameInput(), $name];

			$content = str_replace($find, $replace, $content);

            if (!$this->files->exists($path)) {
                $this->files->makeDirectory($path);
            }

			file_put_contents($path . $name . '.blade.php', $content);
		} else {
			$this->error('Blade already exists!');
		}
	}

	protected function createScss() {
		$name = Str::kebab($this->getNameInput());
		$path = resource_path('sass/blocks/');

		if (!file_exists($path . '_' . $name . '.scss')) {
			$content = file_get_contents(__DIR__ . '/stubs/block.scss.stub');
			$content = str_replace('DummyClass', $name, $content);

			if (!$this->files->exists($path)) {
				$this->files->makeDirectory($path);
			}

			file_put_contents($path . '_' . $name . '.scss', $content);
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

	protected function getComponentFields($name) {
		if (config('storyblok.oauth_token')) {
			$components = Cache::remember('lsb-compontent-list', 600, function() {
				$managementClient = new \Storyblok\ManagementClient(config('storyblok.oauth_token'));

				return collect($managementClient->get('spaces/' . config('storyblok.space_id') . '/components')->getBody()['components']);
			});

			$schema = $components->filter(function ($component) use ($name) {
				return $component['name'] === $name;
			})->first()['schema'];

			$fields = array_map(function($field) {
				return ' * @property-read string ' . $field . "\n";
			}, array_keys($schema));

			$fields = implode($fields);
		} else {
			$fields = '';
		}

		$phpDoc = <<<'PHPDOC'
/**
DummyFields
 */
PHPDOC;

		return str_replace('DummyFields', $fields, $phpDoc);
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
