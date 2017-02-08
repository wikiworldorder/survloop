<?php
namespace SurvLoop;

use Illuminate\Support\ServiceProvider;

class SurvLoopServiceProvider extends ServiceProvider
{
    public function register()
    {
        /*
        $this->app->bind('wikiworldorder-survloop', function()
        {
            return new Demo;
        });
        */
    }

    public function boot()
    {
        require __DIR__ . '/routes.php';
        $migFile = '2016_11_20_000000_SurvLoop_create_tables.php';
        $this->publishes([
              __DIR__.'/Views'         => base_path('resources/views/vendor/survloop'),
              __DIR__.'/Views/auth'     => base_path('resources/views/auth'),
              __DIR__.'/Public'         => base_path('public/survloop'),
              __DIR__.'/Models'         => base_path('app/Models/SurvLoop'),
              __DIR__.'/Models'         => base_path('app/Models'),
              __DIR__.'/Database/' . $migFile 
                  => base_path('database/migrations/' . $migFile),
              __DIR__.'/Database/SurvLoopSeeder.php' 
                  => base_path('database/seeds/SurvLoopSeeder.php'),
        ]);
        //$this->loadViewsFrom(__DIR__ . '/Views', 'survloop');
    }
}