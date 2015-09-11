<?php namespace C4tech\Upload;

use C4tech\Support\Repository as BaseRepository;
use Illuminate\Support\Facades\Storage;

/**
 * Upload Repository
 *
 * Business logic for accessing Upload data.
 */
class Repository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected static $model = 'foundation.models.upload';

    /**
     * @inheritDoc
     */
    public function create($data = [])
    {
        $disk = null;
        if (!empty($data['disk'])) {
            $disk = $data['disk'];
        }

        $path = $data['path'];
        if (empty($path)) {
            throw new Exception('A target file path must be defined');
        }

        if (empty($data['name'])) {
            $data['name'] = $path;
        }

        if (!empty($data['source'])) {
            $this->uploadFile($disk, $data['source'], $path);
        }

        if (!$this->exists($disk, $path)) {
            throw new Exception('The file was not uploaded');
        }

        return parent::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update($data = [])
    {
        $disk = $this->object->disk;

        if (!empty($data['disk']) && $data['disk'] != $disk) {
            $this->removeFile();
            $disk = $data['disk'];
        }

        $path = $data['path'] ?: $this->object->path;
        if (!empty($data['source'])) {
            $this->uploadFile($disk, $data['source'], $path, $path != $this->object->path);
        }

        if (!$this->exists($disk, $path)) {
            throw new Exception('The file was not uploaded');
        }

        return parent::update($data);
    }

    /**
     * Upload File
     * @param  string  $disk        Target filesystem disk
     * @param  mixed   $source      Contents or resource
     * @param  string  $destination Target path
     * @param  boolean $isNew       If true, checks for colliding paths.
     * @return void
     */
    protected function uploadFile($disk, $source, $destination, $isNew = true)
    {
        if ($isNew && Storage::disk($disk)->exists($destination)) {
            throw new Exception('File already exists');
        }

        if (!Storage::disk($disk)->put($destination, $source)) {
            throw new Exception('There was an error saving the file');
        }
    }

    protected function removeFile($disk = null, $path = null)
    {
        if (empty($disk)) {
            $disk = $this->object->disk;
        }

        if (empty($path)) {
            $path = $this->object->path;
        }

        Storage::disk($disk)->delete($path);
    }

    public function exists($disk = null, $path = null)
    {
        if (empty($disk)) {
            $disk = $this->object->disk;
        }

        if (empty($path)) {
            $path = $this->object->path;
        }

        return Storage::disk($disk)->exists($path);
    }
}
