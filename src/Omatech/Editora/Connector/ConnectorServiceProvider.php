<?php

namespace Omatech\Editora\Connector;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Omatech\Editora\Extractor\Extractor;
use Omatech\Editora\Utils\Editora as Utils;

class ConnectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //Publicamos el archivo de configuración
        $this->publishes([
            __DIR__.'/Configuration.php' => config_path('editora.php'),
        ]);

        //Publicamos los controllers
        $this->publishes([
            __DIR__.'/PreviewController.php' => app_path('Http/Controllers/Editora/PreviewController.php'),
            __DIR__.'/GlobalController.php' => app_path('Http/Controllers/Editora/GlobalController.php'),
        ]);

        //Publicamos los assets
        $this->publishes([
            __DIR__.'/Assets/js' => public_path('js'),
            __DIR__.'/Assets/css' => public_path('css'),
            __DIR__.'/Assets/images' => public_path('images'),
        ], 'public');

        //Rutas: després de les de l'app, perquè el catch-all /{language?}/{nice_url?} no les tapi
        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__.'/Routes.php');
        });

        //Directivas de Blade
        include __DIR__.'/Directives/GenerateEditLinkDirective.php';
        include __DIR__.'/Directives/GenerateEditoraEditScriptsDirective.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/Configuration.php', 'editora'
        );

        $name = config('database.default');
        if (config('database.connections.'.$name.'.driver') === 'mysql') {
            config(['database.connections.'.$name.'.timezone' => DatabaseConnection::offset()]);
        }
        // Resolve after the application's providers have finished configuring the connection.
        $this->app->bind('Extractor', static fn () => new Extractor(DatabaseConnection::open()));
        $this->app->bind('Utils', static fn () => new Utils(DatabaseConnection::open()));

        $this->app['router']->aliasMiddleware('setLocale', 'Omatech\Editora\Connector\Middlewares\SetLocaleMiddleware');
    }
}
