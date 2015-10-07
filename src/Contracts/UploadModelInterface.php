<?php namespace C4tech\Upload\Contracts;

use C4tech\Support\Contracts\ModelInterface;

interface UploadModelInterface extends ModelInterface
{
    /**
     * Uploadable
     *
     * Polymorphic relationship to models that have uploads.
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function uploadable();
}
