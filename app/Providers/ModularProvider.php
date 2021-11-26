<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModularProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $modules = config('modular.modules');
        $path = config('modular.path');

        if($modules){
            Route::group([
               'prefix' => ''
            ], function () use ($modules, $path) {
                foreach($modules as $mod => $submodule){
                    foreach ($submodule as $key => $sub){
                        $relativePath = "/$mod/$sub";
                        Route::middleware('web')
                            ->group(function () use ($mod, $sub, $relativePath, $path){
                                $this->getWebRoutes($mod, $sub, $relativePath, $path);
                            });

                        Route::prefix('api')
                            ->middleware('api')
                            ->group(function () use ($mod, $sub, $relativePath, $path){
                                $this->getApiRoutes($mod, $sub, $relativePath, $path);
                            });


                    }
                }
            });
        }
    }

    private function getWebRoutes($mod, $sub, string $relativePath, $path)
    {
        $routesPath = $path.$relativePath.'/Routes/api.php';
        if(file_exists($routesPath)){
            if($mod != config('modular.groupWithoutPrefix')){
                Route::group([
                    'prefix' => strtolower($mod),
                    'middleware' => $this->getMiddleware($mod)
                ],
                    function() use ($mod, $sub, $routesPath){
                        Route::namespace("App\Modules\\$mod\\$sub\Controllers")->group($routesPath);
                    }
                );
            }else{
                Route::namespace("App\Modules\\$mod\\$sub\Controllers")->
                    middleware($this->getMiddleware($mod))->
                    group($routesPath);
            }
        }
    }

    private function getApiRoutes($mod, $sub, string $relativePath, $path)
    {
        $routesPath = $path.$relativePath.'/Routes/api.php';
        if(file_exists($routesPath)){
            Route::group([
                'prefix' => strtolower($mod),
                'middleware' => $this->getMiddleware($mod, 'api')
            ],
                function() use ($mod, $sub, $routesPath){
                Route::namespace("App\Modules\\$mod\\$sub\Controllers")->group($routesPath);
            }
            );
        }
    }

    private function getMiddleware($mod, $key = 'web')
    {
        $middleware = [];
        $config = config('modular.groupMiddleware');
        if(isset($config[$mod])){
            if(array_key_exists($key, $config[$mod])){
                $middleware = array_merge($middleware, $config[$mod][$key]);
            }
        }

        return $middleware;
    }
}
