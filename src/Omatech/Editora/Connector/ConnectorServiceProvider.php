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

        // config() en lloc d'env(): amb config:cache env() retorna null fora dels fitxers de config
        $connection = config('database.connections.'.config('database.default'));
        $db = [
            'dbname' => $connection['database'] ?? null,
            'dbuser' => $connection['username'] ?? null,
            'dbpass' => $connection['password'] ?? null,
            'dbhost' => $connection['host'] ?? null,
            'dbport' => $connection['port'] ?? 3306,
        ];

        $this->app->bind('Extractor', function() use($db) {
            return new Extractor($db);
        });

        $this->app->bind('Utils', function() use ($db) {
            return new Utils($db);
        });

        $this->app['router']->aliasMiddleware('setLocale', 'Omatech\Editora\Connector\Middlewares\SetLocaleMiddleware');
    }
}
