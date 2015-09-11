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

    /**
     * Uploadable
     *
     * Polymorphic relationship to models that have uploads.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function uploadable()
    {
        return $this->morphTo();
    }
}
