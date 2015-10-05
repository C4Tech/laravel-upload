<?php namespace C4tech\Upload;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @inheritDoc
     */
    protected $defer = false;

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->publishes(
            [__DIR__.'/../resources/migrations/' => database_path('migrations')],
            'migrations'
        );

        Facade::boot();
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        App::singleton(
            'c4tech.upload',
            function () {
                $repo = Config::get('upload.repos.upload', 'C4tech\Upload\Repository');
                $repo = Config::get('foundation.repos.upload', $repo);
                return new $repo;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function provides()
    {
        return ['c4tech.upload'];
    }
}
