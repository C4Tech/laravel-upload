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

        $flush_model = function ($uploadable) {
            $repository = $this->make($uploadable);
            $tags = $repository->getTags('uploads');

            if (Config::get('app.debug')) {
                Log::debug(
                    'Flushing uploadable relationship caches',
                    [
                        'uploadable' => $repository->id,
                        'tags' => $tags
                    ]
                );
            }

            Cache::tags($tags)->flush();
        };

        $model::updated($flush_model);
        $model::deleted($flush_model);

        $flush_upload = function ($upload_model) use ($model) {
            $upload = Upload::make($upload_model);
            $tags = $upload->getTags($model);

            if (Config::get('app.debug')) {
                Log::debug(
                    'Flushing inverse upload relationship caches',
                    [
                        'model' => $model,
                        'tags' => $tags
                    ]
                );
            }

            Cache::tags($tags)->flush();
        };

        UploadModel::updated($flush_upload);
        UploadModel::deleted($flush_upload);
    }

    /**
     * @inheritDoc
     */
    public function withUpload(UploadInterface $upload)
    {
        $model = $this->getModelClass();
        return Cache::tags($upload->getTags($model))
            ->remember(
                $upload->getCacheId($model),
                self::CACHE_SHORT,
                function () use ($upload) {
                    $objects = $this->object->hasUpload($upload->getModel())
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
