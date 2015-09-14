<?php namespace C4tech\Test\Upload\Uploadable;

use C4tech\Upload\Facade as Upload;
use C4tech\Support\Test\Repository as TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery;

class RepositoryTraitTest extends TestCase
{
    public function setUp()
    {
        $this->setRepository(
            'C4tech\Test\Upload\Uploadable\MockRepository',
            'C4tech\Test\Upload\Uploadable\MockModel'
        );
    }

    public function tearDown()
    {
        Upload::clearResolvedInstances();
        Cache::clearResolvedInstances();
        Config::clearResolvedInstances();
        Log::clearResolvedInstances();
        parent::tearDown();
    }

    public function testListenToUploadUnconfigured()
    {
        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        Config::shouldReceive('get')
            ->never();

        Log::shouldReceive('debug')
            ->never();

        Upload::shouldReceive('updated')
            ->never();
        Upload::shouldReceive('deleted')
            ->never();

        expect_not($this->repo->listenToUpload());
    }

    public function testListenToUploadDebug()
    {
        $model = Mockery::mock('C4tech\Upload\Model')
            ->makePartial();
        $model->shouldReceive('updated')
            ->with(Mockery::type('callable'))
            ->once();
        $model->shouldReceive('deleted')
            ->with(Mockery::type('callable'))
            ->once();

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        Config::shouldReceive('get')
            ->with('app.debug')
            ->once()
            ->andReturn(true);

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->once();

        Upload::shouldReceive('updated')
            ->with(Mockery::type('callable'))
            ->once();
        Upload::shouldReceive('deleted')
            ->with(Mockery::type('callable'))
            ->once();

        expect_not($this->repo->listenToUpload());
    }

    public function testListenToUploadClosure()
    {
        Config::shouldReceive('get')
            ->with('app.debug')
            ->times(3)
            ->andReturn(false, true, true);

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->twice();


        $tag = 'test-tag';
        $uploadable = Mockery::mock('C4tech\Upload\Contracts\UploadableInterface[getTags]');
        $uploadable->id = 28;

        $uploadable->shouldReceive('getTags')
            ->with('uploads')
            ->once()
            ->andReturn([$tag]);

        Cache::shouldReceive('tags->flush')
            ->with([$tag])
            ->withNoArgs()
            ->once();

        $mock_upload = Mockery::mock('C4tech\Upload\Contracts\UploadInterface');

        $model = Mockery::mock('C4tech\Upload\Model[updated,deleted]');
        $model->shouldReceive('updated')
            ->with(Mockery::type('callable'))
            ->once();
        $model->shouldReceive('deleted')
            ->with(
                Mockery::on(function ($closure) use ($mock_upload) {
                    expect_not($closure($mock_upload));

                    return true;
                })
            )
            ->once();

        $this->repo->shouldReceive('getWithUpload')
            ->with($mock_upload)
            ->once()
            ->andReturn([$uploadable]);

        $morph_tag = 'test-morph';
        $upload = Mockery::mock('C4tech\Upload\Contracts\UploadInterface[getTags]');

        $upload->shouldReceive('getTags')
            ->with($model)
            ->once()
            ->andReturn([$morph_tag]);

        Cache::shouldReceive('tags->flush')
            ->with([$morph_tag])
            ->withNoArgs()
            ->once();

        Upload::shouldReceive('updated')
            ->with(Mockery::type('callable'))
            ->once();
        Upload::shouldReceive('deleted')
            ->with(
                Mockery::on(function ($closure) use ($upload) {
                    expect_not($closure($upload));
                    return true;
                })
            )
            ->once();

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        expect_not($this->repo->listenToUpload());
    }

    public function testWithUpload()
    {
        $model = 'model';
        $mock = Mockery::mock('C4tech\Upload\Contracts\UploadInterface[getModel]');
        $mock->shouldReceive('getModel')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        $this->mocked_model->shouldReceive('hasUpload')
            ->with($model)
            ->once()
            ->andReturn(false);

        expect($this->repo->withUpload($mock))
            ->false();
    }

    public function testGetWithUpload()
    {
        $class = 'ModelClass';
        $tags = ['test-tags'];
        $cache_id = 'cache-query-id';
        $model = 'test-model';

        Config::shouldReceive('get')
            ->with('foundation.models.upload', 'foundation.models.upload')
            ->twice()
            ->andReturn('C4tech\Upload\Model');

        $upload = Mockery::mock('C4tech\Upload\Repository[getTags,getCacheId,withUpload]');
        $upload->id = 14;

        $upload->shouldReceive('getTags')
            ->with($class)
            ->once()
            ->andReturn($tags);

        $upload->shouldReceive('getCacheId')
            ->with($class)
            ->once()
            ->andReturn($cache_id);

        $object = Mockery::mock('C4tech\Upload\Model');
        $collection = Mockery::mock('Illuminate\Support\Collection[map]', [[$object]]);

        $new_object = 'TestObjectRepo';
        $new_collection = 'testing-new';

        $this->repo->shouldReceive('make')
            ->with($object)
            ->once()
            ->andReturn($new_object);

        $collection->shouldReceive('map')
            ->with(Mockery::on(function ($map_closure) use ($object, $new_object) {
                expect($map_closure($object))->equals($new_object);

                return true;
            }))
            ->once()
            ->andReturn($new_collection);

        $this->repo->shouldReceive('withUpload->get')
            ->with($model)
            ->withNoArgs()
            ->once()
            ->andReturn($collection);

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($class);

        Cache::shouldReceive('tags->remember')
            ->with($tags)
            ->with(
                $cache_id,
                Mockery::type('integer'),
                Mockery::on(function ($closure) use ($new_collection) {
                    expect($closure())->equals($new_collection);

                    return true;
                })
            )
            ->once()
            ->andReturn(true);

        expect($this->repo->getWithUpload($upload))->true();
    }

    public function testUploads()
    {
        $this->mocked_model->shouldReceive('uploads')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        expect($this->repo->uploads())
            ->false();
    }

    public function testGetUploads()
    {
        $tag = 'test-tag';
        $object = Mockery::mock('C4tech\Upload\Contracts\UploadModelInterface');
        $new_object = 'Demo!';
        $collection = Mockery::mock('Illuminate\Support\Collection[map]', [[$object]]);
        $new_collection = 'TestCollection';

        $this->repo->shouldReceive('getTags')
            ->with('uploads')
            ->once()
            ->andReturn([$tag]);

        $this->repo->shouldReceive('getCacheId')
            ->with('uploads')
            ->once()
            ->andReturn($tag);

        $this->repo->shouldReceive('uploads->get')
            ->withNoArgs()
            ->once()
            ->andReturn($collection);

        Upload::shouldReceive('make')
            ->with($object)
            ->once()
            ->andReturn($new_object);

        $collection->shouldReceive('map')
            ->with(Mockery::on(function ($closure) use ($object, $new_object) {
                expect($closure($object))->equals($new_object);
                return true;
            }))
            ->once()
            ->andReturn($new_collection);

        Cache::shouldReceive('tags->remember')
            ->with([$tag])
            ->with(
                $tag,
                Mockery::type('integer'),
                Mockery::on(function ($closure) use ($new_collection) {
                    expect($closure())->equals($new_collection);
                    return true;
                })
            )
            ->once()
            ->andReturn(true);

        expect($this->repo->getUploads())->true();
    }
}
