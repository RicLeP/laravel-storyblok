<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StubViewsCommand extends Command
{
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
     * @return void
     */
    public function handle(): void
    {
	    $this->makeDirectories();

	    $client = new \Storyblok\ManagementClient(config('storyblok.oauth_token'));

		$components = collect($client->get('spaces/' . config('storyblok.space_id') . '/components/')->getBody()['components']);

		$components->each(function ($component) {
			$path = resource_path('views/' . str_replace('.', '/', config('storyblok.view_path')) . 'blocks/');
			$filename =  $component['name'] . '.blade.php';

			if ($this->option('overwrite') || !file_exists($path . $filename)) {
				$content = file_get_contents(__DIR__ . '/stubs/blade.stub');
				$content = str_replace('#NAME#', $component['name'], $content);

				$body = '';

				foreach ($component['schema'] as $name => $field) {
					$body = $this->writeBlade($field['type'], $name, $body);
				}

				$content = str_replace('#BODY#', $body, $content);

				file_put_contents($path . $filename, $content);

				$this->info('Created: '. $component['name'] . '.blade.php');
			}
		});
    }

	/**
	 * @return void
	 */
	protected function makeDirectories(): void
	{
		if (!file_exists(resource_path('views/' . rtrim(config('storyblok.view_path'), '.')))) {
			File::makeDirectory(resource_path('views/' . rtrim(config('storyblok.view_path'), '.')));
		}

		if (!file_exists(resource_path('views/' . rtrim(config('storyblok.view_path'), '.') . '/blocks'))) {
			File::makeDirectory(resource_path('views/' . rtrim(config('storyblok.view_path'), '.') . '/blocks'));
		}
	}

	/**
	 * @param $type
	 * @param int|string $name
	 * @param string $body
	 * @return string
	 */
	protected function writeBlade($type, int|string $name, string $body): string
	{
		switch ($type) {
			case 'bloks':
				$body .= "\t" . '@foreach ($block->' . $name . ' as $childBlock)' . "\n";
				$body .= "\t\t" . '{{ $childBlock->render() }}' . "\n";
				$body .= "\t" . '@endforeach' . "\n";
				break;
			case 'datetime':
			case 'number':
			case 'text':
				$body .= "\t" . '<p>{{ $block->' . $name . ' }}</p>' . "\n";
				break;
			case 'multilink':
				$body .= "\t" . '<a href="{{ $block->' . $name . '->cached_url }}"></a>' . "\n";
				break;
			case 'options':
				$body .= "\t" . '@foreach ($block->' . $name . ' as $childBlock)' . "\n";
				$body .= "\t\t\n";
				$body .= "\t" . '@endforeach' . "\n";
				break;
			case 'textarea':
			case 'richtext':
				$body .= "\t" . '<div>{!! $block->' . $name . ' !!}</div>' . "\n";
				break;
			default:
				$body .= "\t" . '{{ $block->' . $name . ' }}' . "\n";
		}

		$body .= "\n";
		return $body;
	}
}
