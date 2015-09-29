<?php namespace C4tech\Upload\Uploadable;

use C4tech\Upload\Contracts\UploadInterface;
use C4tech\Upload\Facade as Upload;
use C4tech\Upload\Model as UploadModel;
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
     * Observe Uploadable models for changes. Should be called from the boot() method.
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
                $tags = $uploadable->getTags('uploads');

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

        $flush_morph = function ($upload) use ($model) {
            $tags = Upload::make($upload)->getTags($model);

            if (Config::get('app.debug')) {
                Log::debug(
                    'Flushing uploadable relationship caches',
                    [
                        'model' => $model,
                        'tags' => $tags
                    ]
                );
            }

            Cache::tags($tags)->flush();
        };

        UploadModel::updated($flush_morph);
        UploadModel::deleted($flush_morph);
    }

    /**
     * With Upload
     *
     * Query for the Models that are linked to the given upload.
     */
    public function withUpload(UploadInterface $upload)
    {
        return $this->object->hasUpload($upload->getModel());
    }

    /**
     * Get With Upload
     *
     * Find all of this Model class that are linked to the upload.
     * @param  C4tech\Upload\Contracts\UploadInterface
     * @return Illuminate\Support\Collection
     */
    public function getWithUpload(UploadInterface $upload)
    {
        $model = $this->getModelClass();
        return Cache::tags($upload->getTags($model))
            ->remember(
                $upload->getCacheId($model),
                self::CACHE_SHORT,
                function () use ($upload) {
                    $objects = $this->withUpload($upload)
                        ->get();

                    if ($objects->count()) {
                        $objects = $objects->map(function ($object) {
                            return $this->make($object);
                        });
                    }

                    return $objects;
                }
            );
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
        return Cache::tags($this->getTags('uploads'))
            ->remember(
                $this->getCacheId('uploads'),
                self::CACHE_LONG,
                function () {
                    $uploads = $this->uploads()->get();

                    if ($uploads->count()) {
                        $uploads = $uploads->map(function ($upload) {
                            return Upload::make($upload);
                        });
                    }

                    return $uploads;
                }
            );
    }
}
