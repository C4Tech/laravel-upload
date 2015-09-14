<?php namespace C4tech\Test\Upload;

use C4tech\Support\Test\Facade;

class FacadeTest extends Facade
{
    protected $facade = 'C4tech\Upload\Facade';

    public function testFacade()
    {
        $this->verifyFacadeAccessor('c4tech.upload');
    }
}
