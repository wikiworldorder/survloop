<?php
/**
  * SurvloopServiceProvider manages which package files and folders need to be copied to elsewhere in the system.
  * This mostly just runs after installation, and perhaps of some other code updates. 
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since 0.0
  */
namespace RockHopSoft\Survloop;

use RockHopSoft\Survloop\SurvloopFacade;
use Illuminate\Support\ServiceProvider;

class SurvloopServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind('survloop', function($app) {
            return new SurvloopFacade();
        });

        $this->loadRoutesFrom(__DIR__ . '/Routes/routes.php');
        //$this->loadViewsFrom(__DIR__ . '/Views', 'survloop');
        
        if ($this->app->runningInConsole()) {
            $libDir = '/vendor/rockhopsoft/survloop-libraries/src/';
            $this->publishes([

                __DIR__ . '/Views' 
                    => base_path('resources/views/vendor/survloop'),

                __DIR__ . '/Models' 
                    => base_path('app/Models'),

                __DIR__ . '/Overrides/FortifyServiceProvider.php' 
                    => base_path('app/Providers/FortifyServiceProvider.php'),

                __DIR__ . '/Overrides/CreateNewUser.php' 
                    => base_path('app/Actions/Fortify/CreateNewUser.php'),

                __DIR__ . '/Overrides/fortify.php' 
                    => base_path('config/fortify.php'),

                __DIR__ . '/Overrides/routes-api.php' 
                    => base_path('routes/api.php'),

                __DIR__ . '/Overrides/routes-web.php' 
                    => base_path('routes/web.php'),

                __DIR__ . '/Overrides/Authenticate.php' 
                    => base_path('app/Http/Middleware/Authenticate.php'),

                __DIR__ . '/Database/2020_09_14_000000_create_survloop_tables.php' 
                    => base_path('database/migrations/2020_09_14_000000_create_survloop_tables.php'),

                __DIR__ . '/Database/SurvloopSeeder.php' 
                    => base_path('database/seeders/SurvloopSeeder.php'),

                base_path('/vendor/rockhopsoft/survloop-images/src') 
                    => base_path('storage/app/up/survloop'),

                base_path($libDir . 'geo/ZipCodeSeeder.php') 
                    => base_path('database/seeders/ZipCodeSeeder.php'),

                base_path($libDir . 'geo/ZipCodeSeeder2.php') 
                    => base_path('database/seeders/ZipCodeSeeder2.php'),

                base_path($libDir . 'geo/ZipCodeSeeder3.php') 
                    => base_path('database/seeders/ZipCodeSeeder3.php'),

                base_path($libDir . 'geo/ZipCodeSeeder4.php') 
                    => base_path('database/seeders/ZipCodeSeeder4.php'),

                base_path($libDir . 'js/zxcvbn.js') 
                    => base_path('public/survloop/zxcvbn.js')

            ]);
        }
    }
}