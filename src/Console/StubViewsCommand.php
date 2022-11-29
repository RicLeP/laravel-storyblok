<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Riclep\Storyblok\Traits\HasChildClasses;
use Storyblok\ApiException;
use Storyblok\ManagementClient;

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
	 * @return void
	 * @throws ApiException
	 */
    public function handle(): void
    {
	    $this->makeDirectories();

	    $client = new ManagementClient(config('storyblok.oauth_token'));

		$components = collect($client->get('spaces/' . config('storyblok.space_id') . '/components/')->getBody()['components']);

		$components->each(function ($component) {
			$path = resource_path('views/' . str_replace('.', '/', config('storyblok.view_path')) . 'blocks/');
			$filename =  $component['name'] . '.blade.php';

			if ($this->option('overwrite') || !file_exists($path . $filename)) {
				$content = file_get_contents(__DIR__ . '/stubs/blade.stub');
				$content = str_replace([
					'#NAME#',
					'#CLASS#'
				], [
					$component['name'],
					$this->getChildClassName('Block', $component['name'])
				], $content);

				$body = '';

				foreach ($component['schema'] as $name => $field) {
					$body = $this->writeBlade($field, $name, $body);
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
	 * @param $field
	 * @param int|string $name
	 * @param string $body
	 * @return string
	 */
	protected function writeBlade($field, int|string $name, string $body): string
	{
		if (!str_starts_with($name, 'tab-')) {
			switch ($field['type']) {
				case 'options':
				case 'bloks':
					$body .= "\t" . '@foreach ($block->' . $name . ' as $childBlock)' . "\n";
					$body .= "\t\t" . '{{ $childBlock->render() }}' . "\n";
					$body .= "\t" . '@endforeach' . "\n";
					break;
				case 'datetime':
					$body .= "\t" . '<time datetime="{{ $block->' . $name . '->content()->toIso8601String() }}">{{ $block->' . $name . ' }}</time>' . "\n";
					break;
				case 'number':
				case 'text':
					$body .= "\t" . '<p>{{ $block->' . $name . ' }}</p>' . "\n";
					break;
				case 'multilink':
					$body .= "\t" . '<a href="{{ $block->' . $name . '->cached_url }}"></a>' . "\n";
					break;
				case 'textarea':
				case 'richtext':
					$body .= "\t" . '<div>{!! $block->' . $name . ' !!}</div>' . "\n";
					break;
				case 'asset':
					if (in_array('images', $field['filetypes'], true)) {
						$body .= "\t" . '@if ($block->' . $name . '->hasFile())' . "\n";
						$body .= "\t\t" . '<img src="{{ $block->' . $name . '->transform()->resize(100, 100) }}" alt>' . "\n";
						$body .= "\t" . '@endif' . "\n";
					} else {
						$body .= "\t" . '{{ $block->' . $name . ' }}' . "\n";
					}
					break;
				default:
					$body .= "\t" . '{{ $block->' . $name . ' }}' . "\n";
			}
		}

		$body .= "\n";
		return $body;
	}
}
