<?php namespace C4tech\Upload;

use C4tech\Support\Model as BaseModel;
use C4tech\Upload\Contracts\UploadInterface;
use Illuminate\Support\Facades\Config;

/**
 * Upload Model
 *
 * An upload.
 */
class Model extends BaseModel implements UploadInterface
{
    /**
     * @inheritdoc
     */
    public $table = 'uploads';

    /**
     * @inheritdoc
     */
    public function getForeignKey()
    {
        return 'upload_id';
    }
}
