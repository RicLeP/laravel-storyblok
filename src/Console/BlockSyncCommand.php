<?php

namespace Riclep\Storyblok\Console;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
    protected $description = 'Sync Storyblok attributes to Laravel block classes.';
    /**
     * @var Filesystem
     */
    private Filesystem $files;

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
    public function handle()
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
    protected function getAllComponents()
    {
        $path = $this->option('path');

        $files = collect($this->files->allFiles($path));

        return $files->map(function ($file) {
            return [
                'class' => Str::of($file->getFilename())->replace('.php', ''),
                'component' => Str::of($file->getFilename())->replace('.php', '')->kebab(),
            ];
        });
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
            $this->info('Component updated successfully.');
        } else {
            $this->error('Component not yet created...');
        }
    }

    protected function getComponentFields($name)
    {
        if (config('storyblok.oauth_token')) {
            $components = Cache::remember('lsb-compontent-list', 600, function () {
                $managementClient = new \Storyblok\ManagementClient(config('storyblok.oauth_token'));

                return collect($managementClient->get('spaces/'.config('storyblok.space_id').'/components')->getBody()['components']);
            });

            $component = $components->firstWhere('name', $name);

            $fields = [];
            foreach ($component['schema'] as $name => $data) {
                if ( ! $this->isIgnoredType($data['type'])) {
                    $fields[$name] = $this->convertToPhpType($data['type']);
                }
            }

            return $fields;
        } else {
            $this->error("Please set your management token in the storkyblok config file");
            return [];
        }
    }

    /**
     * Convert Storyblok types to PHP native types for proper type-hinting
     *
     * @param $type
     * @return string
     */
    protected function convertToPhpType($type)
    {
        switch ($type) {
            case "bloks":
                return "array";
            default:
                return "string";
        }
    }

    /**
     * There are certain Storyblok types that are not useful to model in our component classes. We can use this to
     * filter those types out.
     *
     * @param $type
     * @return bool
     */
    protected function isIgnoredType($type)
    {
        $ignored = ['section'];

        return in_array($type, $ignored);
    }

}
