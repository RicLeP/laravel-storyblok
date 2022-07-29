<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Storyblok\ManagementClient;

class ExportStoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:export {slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save a story as JSON';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->client = new ManagementClient(config('storyblok.oauth_token'));

		parent::__construct();
	}

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
	    $storyExists = $this->client->get('spaces/' . config('storyblok.space_id') . '/stories/', [
			'with_slug' => $this->argument('slug')
	    ])->getBody()['stories'];

		if ($storyExists) {
			$filename = 'storyblok-' . str($this->argument('slug'))->replace('/', '-')->slug() . '.json';

			$story = $this->client->get('spaces/' . config('storyblok.space_id') . '/stories/' . $storyExists[0]['id'])->getBody();

			$json = json_encode($story);

			Storage::put($filename, $json);

			$this->info('Saved to storage: ' . $filename);
		} else {
			$this->warn('There is no story for your slug: ' . $this->argument('slug'));
		}
    }
}
