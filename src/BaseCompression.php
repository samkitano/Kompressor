<?php

namespace samkitano\Kompressor;

use Illuminate\Support\Facades\File;
use samkitano\Kompressor\Exceptions\KompressorException;

class BaseCompression
{
    /** @var string */
    protected $library;

    /** @var string|array */
    protected $compressable;

    /** @var string */
    protected $destination;

    /** @var string */
    protected $name;

    /** @var bool */
    protected $delete;

    /** @var array */
    protected $extract_files;

    /** @var string|null */
    protected $config_path;

    /** @var string */
    protected $config_extension;

    /** @var bool */
    protected $source_is_dir;

    /** @var string */
    protected $glob_pattern;

    /** @var bool */
    protected $source_is_archive;

    /** @var string */
    protected $called_method;

    /** @var bool */
    protected $keep_paths = false; // TODO

    /** @var array */
    protected $data = [];

    /** @var int */
    protected $numFiles = 0;

    /**
     * BaseCompression constructor.
     *
     * @param string        $library
     * @param string|array  $compressable
     * @param string|null   $destination
     * @param string|null   $name
     * @param bool|null     $delete
     * @param array         $extractFiles
     * @param string|null   $globPattern
     */
    function __construct(string $library, $compressable, $destination, $name, $delete, $extractFiles, $globPattern)
    {
        $this->library = strtolower($library);
        $this->compressable = $compressable;
        $this->destination = $destination;
        $this->extract_files = $extractFiles;
        $this->name = $name;
        $this->delete = (bool) $delete;
        $this->glob_pattern = $globPattern;

        $this->called_method = debug_backtrace()[2]['function'];
        $this->setUp();
    }

    /**
     * @return array
     */
    function getData(): array
    {
        return $this->data;
    }

    /**
     * Set up vars and flags
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->checkSourceFiles()
             ->setDefaultConfig()
             ->setFlags();

        $callProperFunction = 'set'.title_case($this->called_method).'Data';

        $this->$callProperFunction();
    }

    /**
     * @return void
     */
    protected function setCompressData(): void
    {
        $compress = new Compress(
            $this->compressable,
            $this->destination,
            $this->name,
            $this->source_is_dir,
            $this->glob_pattern,
            $this->config_extension,
            $this->config_path
        );

        $this->data = $compress->getData();
    }

    /**
     * @return void
     */
    protected function setExtractData(): void
    {
        $extract = new Extract(
            $this->compressable,
            $this->destination,
            $this->extract_files
        );

        $this->data = $extract->getData();
    }

    /**
     * @param int $num
     */
    function setNumFiles(int $num): void
    {
        $this->numFiles = $num;
    }

    /**
     * @return void
     */
    protected function setReadData(): void
    {
        $read = new Read($this->compressable);

        $this->data = $read->getData();
    }

    /**
     * @return void
     */
    protected function setRemoveData(): void
    {
        $remove = new Remove(
            $this->compressable,
            (array) $this->destination
        );

        $this->data = $remove->getData();
    }

    /**
     * @return void
     */
    protected function setAddData(): void
    {
        $add = new Add(
            $this->compressable,
            (array) $this->destination
        );

        $this->data = $add->getData();
    }

    /**
     * @return $this|\samkitano\Kompressor\BaseCompression
     */
    protected function setFlags(): self
    {
        $this->source_is_dir = $this->sourceIsDir();
        $this->source_is_archive = $this->sourceIsArchive();

        return $this;
    }

    /**
     * @return $this|\samkitano\Kompressor\BaseCompression
     */
    protected function setDefaultConfig(): self
    {
        $this->config_path = config('kompressor.default_archives_directory');
        $this->config_extension = $this->normalizeExtension(
            config('kompressor.'.$this->library.'.extension', $this->library)
        );

        return $this;
    }

    /**
     * @return bool
     */
    protected function sourceIsArchive(): bool
    {
        if ($this->source_is_dir) {
            return false;
        }

        $info = is_array($this->compressable)
            ? pathinfo($this->compressable[0])
            : pathinfo($this->compressable);

        $ext = $info['extension'];

        return $this->normalizeExtension($ext) === $this->config_extension;
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

    /**
     * @return $this|\samkitano\Kompressor\BaseCompression
     */
    protected function checkSourceFiles(): self
    {
        $files = (array) $this->compressable;

        foreach ($files as $file) {
            $this->checkSourceFile($file);
        }

        $this->verifyMultipleSources();

        return $this;
    }

    /**
     * @return $this|\samkitano\Kompressor\BaseCompression
     */
    protected function verifyMultipleSources(): self
    {
        if ($this->called_method !== 'compress') {
            return $this;
        }

        $this->checkDifferentSourceDirectories()
            ->checkMultipleSourceAreDirectories();

        return $this;
    }

    /**
     * @param string $file
     *
     * @return void
     * @throws \samkitano\Kompressor\Exceptions\KompressorException
     */
    protected function checkSourceFile(string $file): void
    {
        if (! File::exists($file)) {
            throw new KompressorException("Source '$file' not found.");
        }
    }

    /**
     * @return bool
     */
    protected function sourceIsDir(): bool
    {
        // if an array of files is passed, sure enough the source is not a directory.
        // can not pass multiple source directories.
        if (is_array($this->compressable)) {
            return false;
        }

        return File::isDirectory($this->compressable);
    }

    /**
     * @param string $file
     *
     * @return void
     */
    protected function unlinkFile(string $file): void
    {
        if (File::exists($file)) {
            unlink($file);
        }
    }

    /**
     * @param string|array $files
     *
     * @return void
     */
    protected function processDeletion($files): void
    {
        if ($this->delete) {
            foreach ((array) $files as $file) {
                $this->unlinkFile($file);
            }
        }
    }

    /**
     * @param int $n
     *
     * @return string
     */
    protected function strFile(int $n): string
    {
        $plural = $n > 1 ? 's' : '';

        return 'file'.$plural;
    }

    /**
     * @return string
     */
    protected function getCompressResponse(): string
    {
        $open_file = $this->data['destination_dir'] . DIRECTORY_SEPARATOR . $this->data['destination_file_name'];
        $n_files = count($this->data['source_files']);
        $word = $this->strFile($n_files);

        return "Created $open_file with $n_files $word";
    }

    /**
     * @return string
     */
    protected function getExtractResponse(): string
    {
        $numFiles = isset($this->data['extract_files'])
            ? count($this->data['extract_files'])
            : $this->numFiles;

        return "Extracted {$numFiles} "
            ."{$this->strFile($numFiles)} "
            . "from {$this->data['source_archive']}";
    }

    /**
     * @return string
     */
    protected function getAddResponse(): string
    {
        return "{$this->numFiles} {$this->strFile($this->numFiles)} "
            . "added to {$this->data['source']}";
    }

    /**
     * @return string
     */
    protected function getRemoveResponse(): string
    {
        return "{$this->numFiles} {$this->strFile($this->numFiles)} "
            . "removed from {$this->data['source']}";
    }

    /**
     * @return string
     */
    protected function getResponse(): string
    {
        $callProperFunction = 'get'.title_case($this->called_method).'Response';

        return $this->$callProperFunction();
    }

    /**
     * @return bool
     */
    protected function hasMultipleSourceDirs(): bool
    {
        if ($this->sourceIsSingle()) {
            return false;
        }

        $dir_names = [];

        foreach ((array) $this->compressable as $file) {
            $dir_names[] = dirname($file);
        }

        $unique = array_unique($dir_names);

        return count($unique) > 1;
    }

    /**
     * @return bool
     */
    protected function sourceIsSingle(): bool
    {
        if (! is_array($this->compressable)) {
            return true;
        }

        $arr_files = (array) $this->compressable;

        if (count($arr_files) === 1) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function hasProperDestination(): bool
    {
        return isset($this->destination) && isset($this->name);
    }

    /**
     * @return $this|\samkitano\Kompressor\BaseCompression
     * @throws \samkitano\Kompressor\Exceptions\KompressorException
     */
    protected function checkDifferentSourceDirectories(): self
    {
        if ($this->hasMultipleSourceDirs() && ! $this->hasProperDestination()) {
            throw new KompressorException("Multiple source directories detected. Please see documentation.");
        }

        return $this;
    }

    /**
     * @return $this|\samkitano\Kompressor\BaseCompression
     * @throws \samkitano\Kompressor\Exceptions\KompressorException
     */
    protected function checkMultipleSourceAreDirectories(): self
    {
        if ($this->sourceIsSingle()) {
            return $this;
        }

        if ($this->sourcesAreMultipleDirs()) {
            throw new KompressorException("Sorry, can not archive multiple directories, or mixed sources!");
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function sourcesAreMultipleDirs(): bool
    {
        $is_dir = 0;
        $is_file = 0;
        $arr_files = (array) $this->compressable;

        foreach ($arr_files as $file) {
            if (File::isDirectory($file)) {
                $is_dir++;
            } else {
                if (File::exists($file)) {
                    $is_file++;
                }
            }

            if ($is_dir > 1 || ($is_dir && $is_file)) {
                return true;
            }
        }

        return false;
    }
}
