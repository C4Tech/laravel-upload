<?php namespace C4tech\Test\Upload;

use C4tech\Support\Test\Repository as TestCase;
use Exception;
use Illuminate\Support\Facades\Storage;
use Mockery;

class RepositoryTest extends TestCase
{

    public function setUp()
    {
        $this->setRepository('C4tech\Upload\Repository', 'C4tech\Upload\Model');
        $this->repo->shouldAllowMockingProtectedMethods();
    }

    public function tearDown()
    {
        Storage::clearResolvedInstances();
        parent::tearDown();
    }

    /**
     * @expectedException C4tech\Upload\Exception
     */
    public function testCreateMissingPath()
    {
        $data = [];

        $this->repo->shouldReceive('uploadFile')
            ->never();

        $this->repo->shouldReceive('exists')
            ->never();

        expect($this->repo->create($data))->equals(null);
    }

    /**
     * @expectedException C4tech\Upload\Exception
     */
    public function testCreateBasicDoesntExists()
    {
        $disk = null;
        $path = 'test/path';
        $data = [
            'path' => $path
        ];

        $real_data = $data;
        $real_data['name'] = $path;

        $this->repo->shouldReceive('uploadFile')
            ->never();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(false);

        expect($this->repo->create($data))->equals(null);
    }

    public function testCreateAlreadyExists()
    {
        $disk = null;
        $path = 'test/path';
        $data = [
            'path' => $path
        ];

        $real_data = $data;
        $real_data['name'] = $path;

        $this->repo->shouldReceive('uploadFile')
            ->never();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(true);

        $this->stubCreate($real_data);
        expect($this->repo->create($data))->true();
    }

    public function testCreateUploadSuccess()
    {
        $disk = 'other-disk';
        $path = 'test/path';
        $contents = 'contents';
        $data = [
            'disk' => $disk,
            'path' => $path,
            'name' => 'test-name',
            'source' => $contents
        ];

        $real_data = $data;
        unset($real_data['source']);

        $this->repo->shouldReceive('uploadFile')
            ->with($disk, $contents, $path)
            ->once();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(true);

        $this->stubCreate($real_data);
        expect($this->repo->create($data))->true();
    }

    public function testUpdateMoveDisk()
    {
        $contents = 'old-contents';
        $oldDisk = 'old-disk';
        $disk = 'new-disk';
        $path = 'test/path/old';
        $this->mocked_model->disk = $oldDisk;
        $this->mocked_model->path = $path;

        $data = [
            'disk' => $disk
        ];

        $this->repo->shouldReceive('removeFile')
            ->withNoArgs()
            ->once()
            ->andReturn($contents);

        $this->repo->shouldReceive('uploadFile')
            ->with($disk, $contents, $path, true)
            ->once();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(true);

        $this->stubUpdate($data);
        expect($this->repo->update($data))->true();
    }

    public function testUpdateMoveDiskAndUpload()
    {
        $contents = 'new-contents';
        $oldDisk = 'old-disk';
        $disk = 'new-disk';
        $path = 'test/path/old';
        $this->mocked_model->disk = $oldDisk;
        $this->mocked_model->path = $path;

        $data = [
            'disk' => $disk,
            'source' => $contents
        ];

        $real_data = $data;
        unset($real_data['source']);

        $this->repo->shouldReceive('removeFile')
            ->withNoArgs()
            ->once()
            ->andReturn('old-contents');

        $this->repo->shouldReceive('uploadFile')
            ->with($disk, $contents, $path, true)
            ->once();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(true);

        $this->stubUpdate($real_data);
        expect($this->repo->update($data))->true();
    }

    public function testUpdateMovePath()
    {
        $contents = 'old-contents';
        $disk = 'old-disk';
        $oldPath = 'test/path/old';
        $path = 'test/path/new';
        $this->mocked_model->disk = $disk;
        $this->mocked_model->path = $oldPath;

        $data = [
            'path' => $path
        ];

        $this->repo->shouldReceive('removeFile')
            ->withNoArgs()
            ->once()
            ->andReturn($contents);

        $this->repo->shouldReceive('uploadFile')
            ->with($disk, $contents, $path, true)
            ->once();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(true);

        $this->stubUpdate($data);
        expect($this->repo->update($data))->true();
    }

    public function testUpdateMovePathAndUpload()
    {
        $contents = 'new-contents';
        $disk = 'old-disk';
        $oldPath = 'test/path/old';
        $path = 'test/path/new';
        $this->mocked_model->disk = $disk;
        $this->mocked_model->path = $oldPath;

        $data = [
            'path' => $path,
            'source' => $contents
        ];

        $real_data = $data;
        unset($real_data['source']);

        $this->repo->shouldReceive('removeFile')
            ->withNoArgs()
            ->once()
            ->andReturn('old-contents');

        $this->repo->shouldReceive('uploadFile')
            ->with($disk, $contents, $path, true)
            ->once();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(true);

        $this->stubUpdate($real_data);
        expect($this->repo->update($data))->true();
    }

    public function testUpdateReplace()
    {
        $contents = 'new-contents';
        $disk = 'old-disk';
        $path = 'test/path/old';
        $this->mocked_model->disk = $disk;
        $this->mocked_model->path = $path;

        $data = [
            'source' => $contents
        ];

        $real_data = $data;
        unset($real_data['source']);

        $this->repo->shouldReceive('removeFile')
            ->never();

        $this->repo->shouldReceive('uploadFile')
            ->with($disk, $contents, $path, false)
            ->once();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(true);

        $this->stubUpdate($real_data);
        expect($this->repo->update($data))->true();
    }

    public function testUpdateBasic()
    {
        $disk = 'old-disk';
        $path = 'test/path/old';
        $this->mocked_model->disk = $disk;
        $this->mocked_model->path = $path;

        $data = [
            'meta' => true
        ];

        $this->repo->shouldReceive('removeFile')
            ->never();

        $this->repo->shouldReceive('uploadFile')
            ->never();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(true);

        $this->stubUpdate($data);
        expect($this->repo->update($data))->true();
    }

    /**
     * @expectedException C4tech\Upload\Exception
     */
    public function testUpdateDoesntExists()
    {
        $disk = 'old-disk';
        $path = 'test/path/old';
        $this->mocked_model->disk = $disk;
        $this->mocked_model->path = $path;

        $data = [
            'meta' => true
        ];

        $this->repo->shouldReceive('removeFile')
            ->never();

        $this->repo->shouldReceive('uploadFile')
            ->never();

        $this->repo->shouldReceive('exists')
            ->with($disk, $path)
            ->once()
            ->andReturn(false);

        expect($this->repo->update($data))->equals(null);
    }

    /**
     * @expectedException C4tech\Upload\Exception
     */
    public function testUploadNewAlreadyExists()
    {
        $disk = 'some-disk';
        $target = 'some/path';

        Storage::shouldReceive('disk->exists')
            ->with($disk)
            ->with($target)
            ->once()
            ->andReturn(true);

        expect($this->repo->uploadFile($disk, 'source', $target, true))->equals(null);
    }

    public function testUploadReplaceAlreadyExists()
    {
        $disk = 'some-disk';
        $source = 'contents';
        $target = 'some/path';

        Storage::shouldReceive('disk->exists')
            ->never();

        Storage::shouldReceive('disk->put')
            ->with($disk)
            ->with($target, $source)
            ->once()
            ->andReturn(true);

        expect($this->repo->uploadFile($disk, $source, $target, false))->equals(null);
    }

    /**
     * @expectedException C4tech\Upload\Exception
     */
    public function testUploadFails()
    {
        $disk = 'some-disk';
        $source = 'contents';
        $target = 'some/path';

        Storage::shouldReceive('disk->exists')
            ->never();

        Storage::shouldReceive('disk->put')
            ->with($disk)
            ->with($target, $source)
            ->once()
            ->andReturn(false);

        expect($this->repo->uploadFile($disk, $source, $target, false))->equals(null);
    }

    public function testRemoveDoesntExist()
    {
        $this->repo->shouldReceive('exists')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        expect($this->repo->removeFile())->null();
    }

    public function testRemoveExists()
    {
        $path = 'path/to/old';
        $contents = 'contents';

        $this->repo->shouldReceive('exists')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->repo->shouldReceive('getPath')
            ->withNoArgs()
            ->twice()
            ->andReturn($path);

        Storage::shouldReceive('disk->get')
            ->withNoArgs()
            ->with($path)
            ->once()
            ->andReturn($contents);

        Storage::shouldReceive('disk->delete')
            ->withNoArgs()
            ->with($path)
            ->once();

        expect($this->repo->removeFile())->equals($contents);
    }

    public function testExistsDefaults()
    {
        $path = 'path/to/old';
        $return = true;

        $this->repo->shouldReceive('getPath')
            ->with(null)
            ->once()
            ->andReturn($path);

        $this->repo->shouldReceive('getDisk->exists')
            ->with(null)
            ->with($path)
            ->once()
            ->andReturn($return);

        expect($this->repo->exists())->equals($return);
    }

    public function testExistsSpecified()
    {
        $disk = 'given-disk';
        $path = 'path/to/given';
        $return = false;

        $this->repo->shouldReceive('getPath')
            ->with($path)
            ->once()
            ->andReturn($path);

        $this->repo->shouldReceive('getDisk->exists')
            ->with($disk)
            ->with($path)
            ->once()
            ->andReturn($return);

        expect($this->repo->exists($disk, $path))->equals($return);
    }

    public function testGetFilePathDefaults()
    {
        $path = 'path';
        $real_path = '/real/path';

        $this->repo->shouldReceive('getPath')
            ->with(null)
            ->once()
            ->andReturn($path);

        $this->repo->shouldReceive('getDisk->getDriver->getAdapter->applyPathPrefix')
            ->with(null)
            ->withNoArgs()
            ->withNoArgs()
            ->with($path)
            ->once()
            ->andReturn($real_path);

        expect($this->repo->getFilePath())->equals($real_path);
    }

    public function testGetFilePathSpecified()
    {
        $disk = 'disk';
        $path = 'path';
        $real_path = '/real/path';

        $this->repo->shouldReceive('getPath')
            ->with($path)
            ->once()
            ->andReturn($path);

        $this->repo->shouldReceive('getDisk->getDriver->getAdapter->applyPathPrefix')
            ->with($disk)
            ->withNoArgs()
            ->withNoArgs()
            ->with($path)
            ->once()
            ->andReturn($real_path);

        expect($this->repo->getFilePath($disk, $path))->equals($real_path);
    }

    public function testGetMimeDefaults()
    {
        $path = 'path';
        $mime = 'meme/cat';

        $this->repo->shouldReceive('getPath')
            ->with(null)
            ->once()
            ->andReturn($path);

        $this->repo->shouldReceive('getDisk->mimeType')
            ->with(null)
            ->with($path)
            ->once()
            ->andReturn($mime);

        expect($this->repo->getMime())->equals($mime);
    }

    public function testGetMimeSpecified()
    {
        $disk = 'disk';
        $path = 'path';
        $mime = 'meme/rage';

        $this->repo->shouldReceive('getPath')
            ->with($path)
            ->once()
            ->andReturn($path);

        $this->repo->shouldReceive('getDisk->mimeType')
            ->with($disk)
            ->with($path)
            ->once()
            ->andReturn($mime);

        expect($this->repo->getMime($disk, $path))->equals($mime);
    }

    public function testGetDiskDefaults()
    {
        $disk = $this->mocked_model->disk = 'somewhere';
        $result = 'magic';

        Storage::shouldReceive('disk')
            ->with($disk)
            ->once()
            ->andReturn($result);

        expect($this->repo->getDisk())->equals($result);
    }

    public function testGetDiskSpecified()
    {
        $disk = 'disk';
        $result = 'magic';

        Storage::shouldReceive('disk')
            ->with($disk)
            ->once()
            ->andReturn($result);

        expect($this->repo->getDisk($disk))->equals($result);
    }

    public function testGetPathDefaults()
    {
        $path = $this->mocked_model->path = 'path';

        expect($this->repo->getPath())->equals($path);
    }

    public function testGetPathSpecified()
    {
        $path = 'path';

        expect($this->repo->getPath($path))->equals($path);
    }
}
