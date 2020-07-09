<?php

namespace Riclep\Storyblok;

use Illuminate\Support\ServiceProvider;
use Storyblok\Client;

class StoryblokServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
       /* $this->loadRoutesFrom(__DIR__.'/routes/routes.php');

        if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__.'/../config/storyblok.php' => config_path('storyblok.php'),
				__DIR__.'/../stubs/DefaultPage.stub' => app_path('Storyblok') . '/DefaultPage.php',
				__DIR__.'/../stubs/DefaultBlock.stub' => app_path('Storyblok') . '/DefaultBlock.php',
				__DIR__.'/../stubs/DefaultFolder.stub' => app_path('Storyblok') . '/DefaultFolder.php',
				__DIR__.'/../stubs/DefaultAsset.stub' => app_path('Storyblok') . '/DefaultAsset.php',
			], 'storyblok');
        }*/
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/storyblok.php', 'storyblok');

        // Register the main class to use with the facade
      /*  $this->app->singleton('storyblok', function () {
            return new xStoryblok;
        });*/

		////////////TODO should this be a middleware
		$storyblokRequest = $this->app['request']->query->get('_storyblok_tk');
		if (!empty($storyblokRequest)) {
			$pre_token = $storyblokRequest['space_id'] . ':' . config('storyblok.api_preview_key') . ':' . $storyblokRequest['timestamp'];
			$token = sha1($pre_token);
			if ($token == $storyblokRequest['token'] && (int)$storyblokRequest['timestamp'] > strtotime('now') - 3600) {
				config(['storyblok.edit_mode' => true]);
				config(['storyblok.draft' => true]);
			}
		}

        // register the Storyblok client, checking if we are in edit more of the dev requests draft content
		if (config('storyblok.draft')) {
			$client = new Client(config('storyblok.api_preview_key'));
		} else {
			$client = new Client(config('storyblok.api_public_key'));
		}

		$client->editMode(config('storyblok.draft'));

		$this->app->instance('Storyblok\Client', $client);
    }
}
