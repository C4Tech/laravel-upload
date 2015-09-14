<?php namespace C4tech\Test\Upload\Uploadable;

use C4tech\Upload\Contracts\UploadableInterface;
use C4tech\Upload\Uploadable\RepositoryTrait;
use C4tech\Support\Repository;

class MockRepository extends Repository implements UploadableInterface
{
    use RepositoryTrait;
}
