<?php namespace C4tech\Upload\Contracts;

use C4tech\Support\Contracts\ModelInterface;

interface UploadableModelInterface extends ModelInterface
{
    /**
     * Scope: Has Upload
     *
     * Find all of this Model that are linked to the upload.
     * @param  C4tech\Upload\Contracts\UploadModelInterface
     * @return Illuminate\Support\Collection
     */
    public function scopeHasUpload($query, UploadModelInterface $upload);
}
