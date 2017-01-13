<?php

namespace Omatech\Editora\Connector;

use Illuminate\Support\ServiceProvider;
use Omatech\Editora\Utils\Editora;
use Omatech\Editora\Extractor\Extractor;

class ConnectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/EditoraConfig.php' => config_path('editora.php'),
        ]);

        include __DIR__.'/Routes.php';

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/EditoraConfig.php', 'editora'
        );

        $this->db = [
            'dbname' => env('DB_DATABASE'),
            'dbuser' => env('DB_USERNAME'),
            'dbpass' => env('DB_PASSWORD'),
            'dbhost' => env('DB_HOST'),
        ];

        $this->app->singleton('Extractor', function ($app) {
            return new Extractor($this->db);
        });

        $this->app->singleton('Editora', function ($app) {
            return new Editora($this->db);
        });

        $this->app['router']->middleware('setLocale', 'Omatech\Editora\Connector\SetLocaleMiddleware');
    }
}
