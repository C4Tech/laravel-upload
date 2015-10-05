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

    public function testListenToUploadTriggers()
    {
        $model = Mockery::mock('Uploadable');
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

        $upload = Mockery::mock('Upload');
        $upload->shouldReceive('updated')
            ->with(Mockery::type('callable'))
            ->once();
        $upload->shouldReceive('deleted')
            ->with(Mockery::type('callable'))
            ->once();

        Upload::shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($upload);

        expect_not($this->repo->listenToUpload());
    }

    public function testListenToUploadUploadableClosure()
    {
        $model = Mockery::mock('UploadableModel');
        $repository = Mockery::mock('C4tech\Upload\Contracts\UploadableInterface');
        $repository->id = 16;
        $tags = ['tags'];
        $upload = Mockery::mock('UploadInstance');
        $uploadable = Mockery::mock('C4tech\Upload\Contracts\UploadableModelInterface');

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        Config::shouldReceive('get')
            ->with('app.debug')
            ->twice()
            ->andReturn(true);

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->twice();

        $model->shouldReceive('updated');
        $model->shouldReceive('deleted')
            ->with(Mockery::on(function ($closure) use ($uploadable) {
                expect_not($closure($uploadable));

                return true;
            }));

        $this->repo->shouldReceive('make')
            ->with($uploadable)
            ->once()
            ->andReturn($repository);

        $repository->shouldReceive('getTags')
            ->with('uploads')
            ->once()
            ->andReturn($tags);

        Cache::shouldReceive('tags->flush')
            ->with($tags)
            ->withNoArgs()
            ->once();

        Upload::shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($upload);

        $upload->shouldReceive('updated');
        $upload->shouldReceive('deleted');

        expect_not($this->repo->listenToUpload());
    }

    public function testListenToUploadModelClosure()
    {
        $model = Mockery::mock('UploadableModel');
        $repository = Mockery::mock('C4tech\Upload\Contracts\UploadInterface');
        $repository->id = 16;
        $tags = ['tags'];
        $upload = Mockery::mock('UploadInstance');
        $uploadable = Mockery::mock('C4tech\Upload\Contracts\UploadableModelInterface');

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

        Config::shouldReceive('get')
            ->with('app.debug')
            ->twice()
            ->andReturn(true);

        Log::shouldReceive('debug')
            ->with(Mockery::type('string'), Mockery::type('array'))
            ->twice();

        $model->shouldReceive('updated');
        $model->shouldReceive('deleted');

        Upload::shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($upload);

        $upload->shouldReceive('updated');
        $upload->shouldReceive('deleted')
            ->with(Mockery::on(function ($closure) use ($upload) {
                expect_not($closure($upload));

                return true;
            }));

        Upload::shouldReceive('make')
            ->with($upload)
            ->once()
            ->andReturn($repository);

        $repository->shouldReceive('getTags')
            ->with($model)
            ->once()
            ->andReturn($tags);

        Cache::shouldReceive('tags->flush')
            ->with($tags)
            ->withNoArgs()
            ->once();

        $this->repo->shouldReceive('withUpload')
            ->with($repository)
            ->once()
            ->andreturn([$uploadable]);

        $uploadable->shouldReceive('getModel->touch')
            ->withNoArgs()
            ->once();

        expect_not($this->repo->listenToUpload());
    }

    public function testWithUpload()
    {
        $class = 'ModelClass';
        $tags = ['test-tags'];
        $cache_id = 'cache-query-id';
        $model = 'test-model';

        $this->repo->shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($class);

        $upload = Mockery::mock('C4tech\Upload\Contracts\UploadInterface');
        $upload->id = 14;

        $upload->shouldReceive('getTags')
            ->with($class)
            ->once()
            ->andReturn($tags);

        $upload->shouldReceive('getCacheId')
            ->with($class)
            ->once()
            ->andReturn($cache_id);

        $upload->shouldReceive('getModel')
            ->withNoArgs()
            ->once()
            ->andReturn($model);

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

        $this->mocked_model->shouldReceive('hasUpload->get')
            ->with($model)
            ->withNoArgs()
            ->once()
            ->andReturn($collection);

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

        expect($this->repo->withUpload($upload))->true();
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
