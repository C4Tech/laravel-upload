<?php namespace C4tech\Test\Upload\Uploadable;

use C4tech\Support\Test\Model as TestCase;
use C4tech\Upload\Facade as Upload;
use Illuminate\Support\Facades\Config;
use Mockery;

class ModelTraitTest extends TestCase
{
    public function setUp()
    {
        $this->setModel('C4tech\Test\Upload\Uploadable\MockModel');
    }

    public function tearDown()
    {
        Upload::clearResolvedInstances();
        parent::tearDown();
    }

    public function testUploads()
    {
        $model = 'C4tech\Upload\Model';
        Upload::shouldReceive('getModelClass')
            ->withNoArgs()
            ->once()
            ->andReturn($model);
        $this->verifyMorphMany('uploads', $model, 'uploadable');
    }

    public function testScopeHasUpload()
    {
        $right = Mockery::mock('C4tech\Upload\Model')->makePartial();
        $right->id = 10;
        $query = $this->getQueryMock();
        $query->shouldReceive('whereHas')
            ->with(
                'uploads',
                Mockery::on(function ($closure) use ($right) {
                    $sql = $this->getQueryMock();
                    $sql->shouldReceive('find')
                        ->with($right->id)
                        ->once()
                        ->andReturn(true);

                    expect($closure($sql))->true();
                    return true;
                })
            )->once()
            ->andReturn(true);

        expect($this->model->scopeHasUpload($query, $right))->true();
    }
}
