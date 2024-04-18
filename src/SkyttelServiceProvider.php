<?php

namespace Ragnarok\Skyttel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Ragnarok\Skyttel\Services\SkyttelFiles;
use Ragnarok\Skyttel\Services\SkyttelImporter;
use Ragnarok\Skyttel\Sinks\SinkSkyttel;
use Ragnarok\Sink\Facades\SinkRegistrar;

class SkyttelServiceProvider extends ServiceProvider
{
    public $singletons = [
        SkyttelFiles::class => SkyttelFiles::class,
        SkyttelImporter::class => SkyttelImporter::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        SinkRegistrar::register(SinkSkyttel::class);
        // $this->registerRoutes();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ragnarok_skyttel.php', 'ragnarok_skyttel');
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
    * Get route group configuration array.
    *
    * @return array
    */
    private function routeConfiguration(): array
    {
        return [
            'namespace'  => "Ragnarok\Skyttel\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api'
        ];
    }

    /**
     * Publish Config
     *
     * @return void
     */
    public function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ragnarok_skyttel.php' => config_path('ragnarok_skyttel.php'),
            ], ['config', 'ragnarok_skyttel', 'ragnarok_skyttel.config']);
        }
    }
}
