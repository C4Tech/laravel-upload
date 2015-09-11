<?php namespace C4tech\Upload;

use C4tech\Support\Repository as BaseRepository;

/**
 * Upload Repository
 *
 * Business logic for accessing Upload data.
 */
class Repository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected static $model = 'foundation.models.upload';
}
