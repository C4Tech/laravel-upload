<?php namespace C4tech\Test\Upload;

use C4tech\Upload\Facade as Upload;
use C4tech\Upload\Repository;
use C4tech\Support\Test\Base as TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Mockery;

class ServiceProviderTest extends TestCase
{
    public function setUp()
    {
        $this->provider = Mockery::mock('C4tech\Upload\ServiceProvider')
            ->makePartial();
    }

    public function tearDown()
    {
        App::clearResolvedInstances();
        Upload::clearResolvedInstances();
        parent::tearDown();
    }

    public function testBoot()
    {
        include_once('helpers.php');
        $this->provider->shouldAllowMockingProtectedMethods()
            ->shouldReceive('publishes')
            ->with(
                Mockery::on(function ($configMapping) {
                    $keys = array_keys($configMapping);
                    $key = array_pop($keys);
                    $value = array_pop($configMapping);
                    expect($key)->contains('/resources/migrations');
                    expect($value)->equals('test/migrations');

                    return true;
                }),
                'migrations'
            )->once();

        Upload::shouldReceive('boot')->once();

        expect_not($this->provider->boot());
    }

    public function testRegister()
    {
        $repo = '\stdClass';
        Config::shouldReceive('get')
            ->with('upload.repos.upload', 'C4tech\Upload\Repository')
            ->once()
            ->andReturn($repo);

        Config::shouldReceive('get')
            ->with('foundation.repos.upload', $repo)
            ->once()
            ->andReturn($repo);

        App::shouldReceive('singleton')
            ->with(
                'c4tech.upload',
                Mockery::on(function ($closure) {
                    $result = $closure();
                    expect_that($result);
                    expect(is_object($result))->true();
                    expect($result instanceof \stdClass)->true();
                    return true;
                })
            )->once();

        expect_not($this->provider->register());
    }

    public function testRegisterFoundation()
    {
        $repo = '\stdClass';
        Config::shouldReceive('get')
            ->with('upload.repos.upload', 'C4tech\Upload\Repository')
            ->once()
            ->andReturn('App\Model');

        Config::shouldReceive('get')
            ->with('foundation.repos.upload', 'App\Model')
            ->once()
            ->andReturn($repo);

        App::shouldReceive('singleton')
            ->with(
                'c4tech.upload',
                Mockery::on(function ($closure) {
                    $result = $closure();
                    expect_that($result);
                    expect(is_object($result))->true();
                    expect($result instanceof \stdClass)->true();
                    return true;
                })
            )->once();

        expect_not($this->provider->register());
    }

    public function testProvides()
    {
        expect($this->provider->provides())
            ->equals(['c4tech.upload']);
    }
}
