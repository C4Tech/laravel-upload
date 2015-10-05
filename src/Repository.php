<?php namespace C4tech\Upload;

use C4tech\Support\Repository as BaseRepository;
use C4tech\Upload\Contracts\UploadInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

/**
 * Upload Repository
 *
 * Business logic for accessing Upload data.
 */
class Repository extends BaseRepository implements UploadInterface
{
    /**
     * @inheritDoc
     */
    protected static $model = '.models.upload';

    /**
     * @inheritDoc
     */
    public function getModelClass()
    {
        $class = Config::get('upload' . static::$model, 'upload' . static::$model);
        return Config::get('foundation' . static::$model, $class);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data = [])
    {
        $disk = null;
        if (!empty($data['disk'])) {
            $disk = $data['disk'];
        }

        if (empty($data['path'])) {
            throw new Exception('A target file path must be defined');
        }
        $path = $data['path'];

        if (empty($data['name'])) {
            $data['name'] = $path;
        }

        if (!empty($data['source'])) {
            $this->uploadFile($disk, $data['source'], $path);
            unset($data['source']);
        }

        if (!$this->exists($disk, $path)) {
            throw new Exception('The file was not uploaded');
        }

        return parent::create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(array $data = [])
    {
        $disk = $this->object->disk;
        $path = $this->object->path;
        $isNew = false;

        if (!empty($data['disk']) && $data['disk'] != $disk) {
            $contents = $this->removeFile();
            $disk = $data['disk'];
            $isNew = true;

            if (empty($data['source'])) {
                $data['source'] = $contents;
            }

            unset($contents);
        }

        if (!empty($data['path'])) {
            $path = $data['path'];
            $contents = $this->removeFile();

            if (empty($data['source'])) {
                $data['source'] = $contents;
            }

            unset($contents);
        }

        if (!empty($data['source'])) {
            $isNew = $isNew ?: $path != $this->object->path;
            $this->uploadFile($disk, $data['source'], $path, $isNew);
            unset($data['source']);
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

    protected function removeFile()
    {
        if (!$this->exists()) {
            return null;
        }

        $contents = $this->getDisk()->get($this->getPath());

        $this->getDisk()->delete($this->getPath());

        return $contents;
    }

    public function exists($disk = null, $path = null)
    {
        return $this->getDisk($disk)->exists($this->getPath($path));
    }

    public function getFilePath($disk = null, $path = null)
    {
        return $this->getDisk($disk)
            ->getDriver()
            ->getAdapter()
            ->applyPathPrefix($this->getPath($path));
    }

    public function getMime($disk = null, $path = null)
    {
        return $this->getDisk($disk)->mimeType($this->getPath($path));
    }

    protected function getDisk($disk = null)
    {
        if (empty($disk)) {
            $disk = $this->object->disk;
        }

        return Storage::disk($disk);
    }

    protected function getPath($path = null)
    {
        if (empty($path)) {
            $path = $this->object->path;
        }

        return $path;
    }
}
