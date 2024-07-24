<?php

namespace Tests\Unit\Services;

use App\Services\Contracts\FileServiceContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileServiceTest extends TestCase
{
    const FILE_NAME = 'image.png';
    protected FileServiceContract $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FileServiceContract::class); // FileService
        Storage::fake('public');
    }

    public function test_success_with_the_valid_file()
    {
        $uploadedFile = $this->uploadTestFile();
        $this->assertTrue(Storage::exists($uploadedFile));
        $this->assertEquals('public', Storage::getVisibility($uploadedFile));
    }

    public function test_it_returns_the_same_path_for_string_file()
    {
        $fileName = 'test/image.png';
        $uploadedFile = $this->service->upload($fileName);

        $this->assertEquals($fileName, $uploadedFile);
    }

    public function test_it_returns_path_without_storage_name()
    {
        $fileName = 'public/storage/test/image.png';
        $uploadedFile = $this->service->upload($fileName);

        $this->assertEquals('/test/image.png', $uploadedFile);
    }

    public function test_success_with_the_valid_file_and_additional_path()
    {
        $folder = 'test';

        $this->assertFalse(Storage::directoryExists($folder));

        $uploadedFile = $this->uploadTestFile(additionalPath: $folder);

        $this->assertTrue(Storage::directoryExists($folder));
        $this->assertTrue(Storage::exists($uploadedFile));
        $this->assertEquals('public', Storage::getVisibility($uploadedFile));
    }

    public function test_remove_file()
    {
        $uploadedFile = $this->uploadTestFile();

        $this->assertTrue(Storage::exists($uploadedFile));

        $this->service->remove($uploadedFile);

        $this->assertFalse(Storage::exists($uploadedFile));
    }

    protected function uploadTestFile(string $fileName = null, string $additionalPath = ''): string
    {
        $file = UploadedFile::fake()->image($fileName ?? self::FILE_NAME);
        return $this->service->upload($file, $additionalPath);
    }
}
