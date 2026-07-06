<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Riclep\Storyblok\Traits\HasChildClasses;
use Storyblok\ManagementApi\Endpoints\ComponentApi;
use Storyblok\ManagementApi\ManagementApiClient;

class StubViewsCommand extends Command
{
    use HasChildClasses;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:stub-views {--O|overwrite}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws ApiException
     */
    public function handle(): void
    {
        $this->makeDirectories();

        $managementClient = new ManagementApiClient(
            personalAccessToken: config('storyblok.oauth_token'),
        );

        $componentApi = new ComponentApi($managementClient, config('storyblok.space_id'));

        $components = collect($componentApi->all()->toArray()['components']);

        $components->each(function ($component) {
            $path = resource_path('views/'.str_replace('.', '/', config('storyblok.view_path')).'blocks/');
            $filename = $component['name'].'.blade.php';

            if ($this->option('overwrite') || ! file_exists($path.$filename)) {
                $content = file_get_contents(__DIR__.'/stubs/blade.stub');
                $content = str_replace([
                    '#NAME#',
                    '#CLASS#',
                ], [
                    $component['name'],
                    $this->getChildClassName('Block', $component['name']),
                ], $content);

                $body = '';

                foreach ($component['schema'] as $name => $field) {
                    $body = $this->writeBlade($field, $name, $body);
                }

                $content = str_replace('#BODY#', $body, $content);

                file_put_contents($path.$filename, $content);

                $this->info('Created View: '.$component['name'].'.blade.php');
            }
        });

        if ($this->option('overwrite') || ! file_exists(resource_path('views/storyblok/pages').'/page.blade.php')) {
            File::copy(__DIR__.'/stubs/page.blade.stub', resource_path('views/storyblok/pages').'/page.blade.php');

            $this->info('Created Page: page.blade.php');

            $this->info('Files created in your views'.DIRECTORY_SEPARATOR.'storyblok folder.');
        }
    }

    protected function makeDirectories(): void
    {
        if (! file_exists(resource_path('views/'.rtrim(config('storyblok.view_path'), '.')))) {
            File::makeDirectory(resource_path('views/'.rtrim(config('storyblok.view_path'), '.')));
        }

        if (! file_exists(resource_path('views/'.rtrim(config('storyblok.view_path'), '.').'/blocks'))) {
            File::makeDirectory(resource_path('views/'.rtrim(config('storyblok.view_path'), '.').'/blocks'));
        }

        if (! file_exists(resource_path('views/'.rtrim(config('storyblok.view_path'), '.').'/pages'))) {
            File::makeDirectory(resource_path('views/'.rtrim(config('storyblok.view_path'), '.').'/pages'));
        }
    }

    protected function writeBlade($field, int|string $name, string $body): string
    {
        if (! str_starts_with($name, 'tab-')) {
            $stub = match ($field['type']) {
                'options', 'bloks' => 'bloks',
                'datetime' => 'datetime',
                'number', 'text' => 'text',
                'multilink' => 'multilink',
                'textarea', 'richtext' => 'textarea',
                'asset' => (array_key_exists('filetypes', $field) && in_array('images', $field['filetypes'], true)) ? 'asset_image' : 'asset_file',
                'image' => 'image',
                'file' => 'file',
                default => 'default',
            };

            $content = file_get_contents(__DIR__.'/stubs/fields/'.$stub.'.stub');
            $content = str_replace('#NAME#', $name, $content);

            $body .= "\t".str_replace("\n", "\n\t", trim($content))."\n";
        }

        $body .= "\n";

        return $body;
    }
}
