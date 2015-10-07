<?php namespace C4tech\Upload\Uploadable;

use C4tech\Upload\Contracts\UploadModelInterface;
use C4tech\Upload\Facade as Upload;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Uploadable Model Trait
 *
 * Common methods for uploadable entities.
 */
trait ModelTrait
{
    /**
     * @inheritDoc
     */
    public function uploads()
    {
        return $this->morphMany(Upload::getModelClass(), 'uploadable');
    }

    /**
     * @inheritDoc
     */
    public function scopeHasUpload($query, UploadModelInterface $upload)
    {
        return $query->whereHas('uploads', function ($sql) use ($upload) {
            return $sql->where($upload->getQualifiedKeyName(), '=', $upload->getKey());
        });
    }
}
