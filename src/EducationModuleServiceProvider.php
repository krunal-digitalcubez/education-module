<?php

namespace Digitalcubez\EducationModule;

use Illuminate\Support\ServiceProvider;

class EducationModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'education-module');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'education-module');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations', 'education-module');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('education-module.php'),
            ], 'config');
            $this->publishes([
                __DIR__ . '/../database/seeders/' => database_path('seeders/'),
            ], 'seeds');
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations/education-module'),
            ], 'migrations');
            $this->publishes([
              __DIR__ . '/../resources/Mutations' => app_path('GraphQL/'),
            ], 'mutations');
            $this->publishes([
              __DIR__ . '/../resources/Queries' => app_path('GraphQL/'),
            ], 'queries');
            $this->publishes([
              __DIR__ . '/../resources/education.graphql' => base_path('graphql/'),
            ], 'graphql');
            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/education-module'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/education-module'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/education-module'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'education-module');

        // Register the main class to use with the facade
        $this->app->singleton('education-module', function () {
            return new EducationModule;
        });
    }
}
