<?php

namespace Riclep\Storyblok;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Riclep\Storyblok\Http\Controllers\StoryblokController;
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
        $this->loadRoutesFrom(__DIR__.'/routes/routes.php');

        if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__.'/../config/storyblok.php' => config_path('storyblok.php'),
				__DIR__.'/../stubs/DefaultPage.stub' => app_path('Storyblok') . '/DefaultPage.php',
				__DIR__.'/../stubs/DefaultBlock.stub' => app_path('Storyblok') . '/DefaultBlock.php',
			], 'storyblok');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/storyblok.php', 'storyblok');

        // Register the main class to use with the facade
        $this->app->singleton('storyblok', function () {
            return new Storyblok;
        });

        // register the Storyblok client
		$client = new Client(config('storyblok.api_key'));
		$this->app->instance('Storyblok\Client', $client);
    }
}
