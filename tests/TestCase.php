<?php

namespace samkitano\Kompressor\Tests;

use Illuminate\Support\Facades\File;
use samkitano\Kompressor\KompressorServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /** @var array */
    protected $test_files;

    /** @var string */
    protected $library;
    protected $extension;
    protected $test_files_dir;
    protected $test_extract_dir;
    protected $test_files_content;
    protected $testing_archive_dir;
    protected $testing_archive_single;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->library = config('kompressor.library');
        $this->extension = $this->getExtension();

        $this->setTestFiles()
             ->setTestFilesDir()
             ->setExtractDir()
             ->setTestFilesContent()
             ->createTestFileSystem();

        $this->testing_archive_single = $this->test_files_dir.DIRECTORY_SEPARATOR.'test1'.$this->extension;
        $this->testing_archive_dir = $this->test_files_dir.DIRECTORY_SEPARATOR.'files'.$this->extension;
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            KompressorServiceProvider::class
        ];
    }

    /**
     * @param null $content
     *
     * @return $this|\samkitano\Kompressor\Tests\TestCase
     */
    protected function setTestFilesContent($content = null): self
    {
        $this->test_files_content = $content ?? '# Kompressor Test';

        return $this;
    }

    /**
     * @param null $files
     *
     * @return $this|\samkitano\Kompressor\Tests\TestCase
     */
    protected function setTestFiles($files = null): self
    {
        $this->test_files = $files ?? [
            'test1.md',
            'test2.md',
            'test3.md',
            'test4.txt',
            'test5.txt',
        ];

        return $this;
    }

    /**
     * @param null $dir
     *
     * @return $this|\samkitano\Kompressor\Tests\TestCase
     */
    protected function setTestFilesDir($dir = null): self
    {
        $this->test_files_dir = $dir ?? __DIR__.DIRECTORY_SEPARATOR.'files';

        return $this;
    }

    /**
     * @param null $dir
     *
     * @return $this|\samkitano\Kompressor\Tests\TestCase
     */
    protected function setExtractDir($dir = null): self
    {
        $this->test_extract_dir = $dir ?? __DIR__.DIRECTORY_SEPARATOR.'extracted';

        return $this;
    }

    /**
     * Create temporary files for testing
     */
    protected function createTestFileSystem(): void
    {
        $this->createTestDirectories()
             ->createTestFiles();
    }

    /**
     * Destroy test temporary files
     */
    protected function destroyTestFileSystem(): void
    {
        $this->destroyTestFiles()
             ->destroyTestDirectories();
    }

    /**
     * @return $this|\samkitano\Kompressor\Tests\TestCase
     */
    protected function createTestDirectories(): self
    {
        $error_message = "Test directories must have read and write permissions.";

        if (! File::isDirectory($this->test_files_dir)) {
            File::makeDirectory($this->test_files_dir, 0777);
        }

        if (! File::isDirectory($this->test_extract_dir)) {
            File::makeDirectory($this->test_extract_dir, 0777);
        }

        if (! File::isWritable($this->test_files_dir) || ! File::isReadable($this->test_files_dir)) {
            $this->fail($error_message);
        }

        if (! File::isWritable($this->test_extract_dir) || ! File::isReadable($this->test_extract_dir)) {
            $this->fail($error_message);
        }

        return $this;
    }

    /**
     * @return $this|\samkitano\Kompressor\Tests\TestCase
     */
    protected function createTestFiles(): self
    {
        foreach ($this->test_files as $test_file) {
            $path = $this->test_files_dir.DIRECTORY_SEPARATOR.$test_file;

            if (File::exists($path)) {
                continue;
            }

            File::append(
                $path,
                $this->test_files_content
            );
        }

        $extra_file = $this->test_extract_dir.DIRECTORY_SEPARATOR.'extra.txt';

        File::append($extra_file, $this->test_files_content);

        return $this;
    }

    /**
     * @return $this|\samkitano\Kompressor\Tests\TestCase
     */
    protected function destroyTestFiles(): self
    {
        File::delete(File::files($this->test_files_dir));
        File::delete(File::files($this->test_extract_dir));

        return $this;
    }

    /**
     * @return $this|\samkitano\Kompressor\Tests\TestCase
     */
    protected function destroyTestDirectories(): self
    {
        File::deleteDirectory($this->test_files_dir);
        File::deleteDirectory($this->test_extract_dir);

        return $this;
    }

    /**
     * @return string
     */
    protected function getExtension(): string
    {
        return $this->normalizeExtension(
            config('kompressor.'.$this->library.'.extension', $this->library)
        );
    }

    /**
     * @param string $extension
     *
     * @return string
     */
    protected function normalizeExtension(string $extension): string
    {
        return '.'.ltrim($extension, '.');
    }
}
