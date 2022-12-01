<?php

namespace Riclep\Storyblok;

use Illuminate\Support\ServiceProvider;
use Riclep\Storyblok\Console\BlockMakeCommand;
use Riclep\Storyblok\Console\BlockSyncCommand;
use Riclep\Storyblok\Console\FolderMakeCommand;
use Riclep\Storyblok\Console\StubViewsCommand;
use Storyblok\Client;

class StoryblokServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
		$this->loadRoutesFrom(__DIR__.'/routes/api.php');

		$this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-storyblok');

        if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__.'/../config/storyblok.php' => config_path('storyblok.php'),
				__DIR__ . '/../stubs/Page.stub' => app_path('Storyblok') . '/Page.php',
				__DIR__ . '/../stubs/Block.stub' => app_path('Storyblok') . '/Block.php',
				__DIR__ . '/../stubs/Asset.stub' => app_path('Storyblok') . '/Asset.php',
				__DIR__ . '/../stubs/Folder.stub' => app_path('Storyblok') . '/Folder.php',
			], 'storyblok');
        }

		$this->commands([
			BlockMakeCommand::class,
			BlockSyncCommand::class,
			FolderMakeCommand::class,
			StubViewsCommand::class
		]);
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/storyblok.php', 'storyblok');

        // Register the main class to use with the facade
		$this->app->singleton('storyblok', function () {
			return new Storyblok;
		});

		////////////TODO should this be a middleware?
	    $storyblokRequest = request()->get('_storyblok_tk');
		if (!empty($storyblokRequest)) {
			$pre_token = $storyblokRequest['space_id'] . ':' . config('storyblok.api_preview_key') . ':' . $storyblokRequest['timestamp'];
			$token = sha1($pre_token);
			if ($token == $storyblokRequest['token'] && (int)$storyblokRequest['timestamp'] > strtotime('now') - 3600) {
				config(['storyblok.edit_mode' => true]);
				config(['storyblok.draft' => true]);
			}
		}

	    // register the Storyblok client, checking if we are in edit more of the dev requests draft content
	    $client = new Client(
			config('storyblok.draft') ? config('storyblok.api_preview_key') : config('storyblok.api_public_key'),
			"api.storyblok.com", "v2", config('storyblok.use_ssl'), config('storyblok.api_region')
	    );

	    // if we’re in Storyblok’s edit mode let’s save that in the config for easy access
	    $client->editMode(config('storyblok.draft'));

	    // This singleton allows to retrieve the driver set has default from the manager
	    $this->app->singleton('image-transformer.driver', function ($app) {
		    return $app['image-transformer']->driver();
	    });

		$this->app->instance('Storyblok\Client', $client);
    }
}
