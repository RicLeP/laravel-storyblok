<?php

namespace Riclep\Storyblok\Console;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Storyblok\ApiException;

class BlockSyncCommand extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ls:sync {component?} {--path=app/Storyblok/Blocks/}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync Storyblok fields to Laravel Block class properties.';
	/**
	 * @var Filesystem
	 */
	private $files;

	/**
	 * Create a new command instance.
	 * @param  Filesystem  $files
	 */
	public function __construct(Filesystem $files)
	{
		parent::__construct();

		$this->files = $files;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		$components = [];
		if ($this->argument('component')) {
			$components = [
				[
					'class' => $this->argument('component'),
					'component' => Str::of($this->argument('component'))->kebab(),
				]
			];
		} else {
			// get all components
			if ($this->confirm("Do you wish to update all components in {$this->option('path')}?")) {
				$components = $this->getAllComponents();
			}
		}

		foreach ($components as $component) {
			$this->info("Updating {$component['component']}");
			$this->updateComponent($component);
		}
	}

	/**
	 * @return \Illuminate\Support\Collection
	 */
	protected function getAllComponents(): \Illuminate\Support\Collection
	{
		$path = $this->option('path');

		$files = collect($this->files->allFiles($path));

		return $files->map(fn($file) => [
			'class' => Str::of($file->getFilename())->replace('.php', ''),
			'component' => Str::of($file->getFilename())->replace('.php', '')->kebab(),
		]);
	}

	private function updateComponent($component): void
	{
		$rootNamespace = "App\Storyblok\Blocks";
		$class = "{$rootNamespace}\\{$component['class']}";

		$reflection = new \ReflectionClass($class);
		$namespace = $reflection->getNamespaceName();
		$path = $this->option('path');
		$originalDoc = $reflection->getDocComment();

		$filepath = $path.$component['class'].'.php';

		$phpdoc = new DocBlock($reflection, new Context($namespace));

		$tags = $phpdoc->getTagsByName('property-read');

		// Clear old attributes
		foreach ($tags as $tag) {
			$phpdoc->deleteTag($tag);
		}

		// Add new attributes
		$fields = $this->getComponentFields($component['component']);
		foreach ($fields as $field => $type) {
			$tagLine = trim("@property-read {$type} {$field}");
			$tag = Tag::createInstance($tagLine, $phpdoc);

			$phpdoc->appendTag($tag);
		}

		// Add default description if none exists
		if ( ! $phpdoc->getText()) {
			$phpdoc->setText("Class representation for Storyblok {$component['component']} component.");
		}

		// Write to file
		if ($this->files->exists($filepath)) {
			$serializer = new Serializer();
			$updatedBlock = $serializer->getDocComment($phpdoc);

			$content = $this->files->get($filepath);

			$content = str_replace($originalDoc, $updatedBlock, $content);

			$this->files->replace($filepath, $content);
			$this->files->chmod($filepath, 0644); // replace() changes permissions

			$this->info('Component updated successfully.');
		} else {
			$this->error('Component not yet created...');
		}
	}

	protected function getComponentFields($name): array
	{
		if (config('storyblok.oauth_token')) {
			$managementClient = new \Storyblok\ManagementClient(config('storyblok.oauth_token'));

			$components = collect($managementClient->get('spaces/'.config('storyblok.space_id').'/components')->getBody()['components']);

			$component = $components->firstWhere('name', $name);

			if( ! $component ){
				$this->error("Storyblok component [{$name}] does not exist.");

				if ($this->confirm('Do you want to create it now?')) {
					$this->createStoryblokCompontent($name);
				}
			}

			$fields = [];
			foreach ($component['schema'] as $name => $data) {
				if ( ! $this->isIgnoredType($data['type'])) {
					$fields[$name] = $this->convertToPhpType($data['type']);
				}
			}

			return $fields;
		}

		$this->error("Please set your management token in the Storyblok config file");
		return [];
	}

	/**
	 * Create a new Storyblok component with given name
	 *
	 * @param  $component_name
	 * @throws ApiException
	 */
	protected function createStoryblokCompontent($component_name){
        $managementClient = new \Storyblok\ManagementClient(config('storyblok.oauth_token'));

        $payload = [
			"component" =>  [
				"name" =>  $component_name,
				"display_name" =>  str::of( str_replace('-', ' ' ,$component_name) )->ucfirst(),
					// "schema" =>  [],
    				// "is_root" =>  false,
					// "is_nestable" =>  true
			]
		];

		$component = $managementClient->post('spaces/'.config('storyblok.space_id').'/components/', $payload)->getBody();

		$this->info("Storyblok component created");

        return $component['component'];
	}

	/**
	 * Convert Storyblok types to PHP native types for proper type-hinting
	 *
	 * @param $type
	 * @return string
	 */
	protected function convertToPhpType($type): string
	{
		return match ($type) {
			"bloks" => "array",
			default => "string",
		};
	}

	/**
	 * There are certain Storyblok types that are not useful to model in our component classes. We can use this to
	 * filter those types out.
	 *
	 * @param $type
	 * @return bool
	 */
	protected function isIgnoredType($type): bool
	{
		$ignored = ['section'];

		return in_array($type, $ignored);
	}

}
