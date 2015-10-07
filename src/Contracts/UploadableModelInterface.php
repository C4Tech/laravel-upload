<?php namespace C4tech\Upload\Contracts;

use C4tech\Support\Contracts\ModelInterface;

interface UploadableModelInterface extends ModelInterface
{
    /**
     * Scope: Has Upload
     *
     * Find all of this Model that are linked to the upload.
     * @param  Illuminate\Database\Query\Builder            $query  Query builder
     * @param  C4tech\Upload\Contracts\UploadModelInterface $upload Upload model
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeHasUpload($query, UploadModelInterface $upload);

    /**
     * Uploads
     *
     * The Uploads associated with this entity.
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function uploads();
}
