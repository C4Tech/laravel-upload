<?php namespace C4tech\Test\Upload\Uploadable;

use C4tech\Upload\Contracts\UploadableModelInterface;
use C4tech\Upload\Uploadable\ModelTrait;
use C4tech\Support\Model;

class MockModel extends Model implements UploadableModelInterface
{
    use ModelTrait;
}
