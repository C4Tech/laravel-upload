<?php namespace C4tech\Upload\Uploadable;

use C4tech\Upload\Contracts\UploadInterface;
use C4tech\Upload\Facade as Upload;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Uploadable Repository Trait
 *
 * Common methods for uploadable repositories.
 */
trait RepositoryTrait
{
    /**
     * Listen to Upload
     *
     * Observe Upload models for changes. Should be called from the boot() method.
     * @return void
     */
    protected function listenToUpload()
    {
        if (!($model = $this->getModelClass())) {
            return;
        }

        if (Config::get('app.debug')) {
            Log::debug('Binding upload relationship caches', ['uploadable' => $model]);
        }

        $flush = function ($upload) {
            foreach ($this->getWithUpload($upload) as $uploadable) {
                $tags = [$this->formatTag($uploadable->getKey(), 'uploads')];

                if (Config::get('app.debug')) {
                    Log::debug(
                        'Flushing upload relationship caches',
                        [
                            'uploadable' => $uploadable->id,
                            'tags' => $tags
                        ]
                    );
                }

                Cache::tags($tags)->flush();
            }
        };

        $model::updated($flush);
        $model::deleted($flush);
    }

    /**
     * Get With Upload
     *
     * Find all of this Model that are linked to the upload.
     * @param  C4tech\Upload\Contracts\UploadInterface
     * @return Illuminate\Support\Collection
     */
    public function getWithUpload(UploadInterface $upload)
    {
        return $this->object->hasUpload($upload)
            ->cacheTags([Upload::formatTag($upload->id, $this->getModelClass())])
            ->remember(static::CACHE_SHORT)
            ->get();
    }

    /**
     * Uploads
     *
     * Query for the Uploads related to this Model.
     */
    public function uploads()
    {
        return $this->object->uploads();
    }

    /**
     * Get Uploads
     *
     * The Uploads related to this Model.
     * @return Illuminate\Support\Collection
     */
    public function getUploads()
    {
        return Cache::tags($this->formatTag('uploads'))
            ->remember(
                $this->getCacheId('uploads'),
                self::CACHE_LONG,
                function () {
                    $uploads = $this->uploads()->get();

                    if ($uploads->count()) {
                        $uploads = $uploads->map(function ($upload) {
                            return static::make($upload);
                        });
                    }

                    return $uploads;
                }
            );
    }
}
