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
     * Uploads
     *
     * The Uploads associated with this entity.
     */
    public function uploads()
    {
        return $this->morphMany(Upload::getModelClass(), 'uploadable');
    }

    /**
     * Scope: Has Upload
     *
     * @param  [type] $query   [description]
     * @param  C4tech\Upload\Contracts\UploadModelInterface
     * @return [type]          [description]
     */
    public function scopeHasUpload($query, UploadModelInterface $upload)
    {
        return $query->whereHas('uploads', function ($sql) use ($upload) {
            return $sql->find($upload->id);
        });

        Log::debug($query->toSql());
    }
}
