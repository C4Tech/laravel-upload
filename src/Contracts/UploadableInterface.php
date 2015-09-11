<?php namespace C4tech\Upload\Contracts;

interface UploadableInterface
{
    /**
     * Get With Upload
     *
     * Find all of this Model that are linked to the upload.
     * @param  C4tech\Upload\Contracts\UploadInterface
     * @return Illuminate\Support\Collection
     */
    public function getWithUpload(UploadInterface $upload);
}
