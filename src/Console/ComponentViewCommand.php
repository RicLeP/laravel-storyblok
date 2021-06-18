<?php

namespace Riclep\Storyblok\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ComponentViewCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:component-list
	            {--additional-fields= : Additional fields to pull form Storyblok Management API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all storyblok components.';

    /**
     * Create a new command instance.
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
    public function handle()
    {
        $managementClient = new \Storyblok\ManagementClient(config('storyblok.oauth_token'));

        $resp = $managementClient->get('spaces/'.config('storyblok.space_id').'/components');
        $components = collect($resp->getBody()['components']);

        $additionalFields = $this->option('additional-fields') ?
            Str::of($this->option('additional-fields'))->explode(',')
            : collect();

        $rows = $components->map(function ($c) use ($additionalFields) {
            $mapped = [
                'name' => $c['name'],
                'display_name' => $c['display_name'],
                'has_image' => $c['image'] ? "<fg=green>true</>" : '<fg=red>false</>',
                'has_template' => $c['preview_tmpl'] ? "<fg=green>true</>" : '<fg=red>false</>',
            ];

            $mappedAdditional = collect($c)->only($additionalFields);

            return array_merge($mapped, $mappedAdditional->toArray());
        });

        $this->table(
            array_keys($rows->first()),
            $rows
        );
    }

}
