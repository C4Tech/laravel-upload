<?php namespace C4tech\Test\Upload;

use C4tech\Support\Test\Model as TestCase;

class ModelTest extends TestCase
{
    public function setUp()
    {
        $this->setModel('C4tech\Upload\Model');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testForeignKey()
    {
        expect($this->model->getForeignKey())->equals('upload_id');
    }

    public function testUploadable()
    {
        $this->verifyMorphTo('uploadable');
    }
}
