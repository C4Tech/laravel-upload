<?php namespace C4tech\Upload\Uploadable;

use C4tech\Upload\Contracts\UploadModelInterface;
use Illuminate\Support\Facades\Config;

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
        return $this->morphToMany(Config::get('foundation.models.upload', 'C4tech\Upload\Model'), 'uploadable');
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
    }
}
