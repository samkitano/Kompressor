<?php

namespace samkitano\Kompressor;

use samkitano\Kompressor\Exceptions\KompressorException;

class Compression
{
    /** @var string */
    protected $library;

    /** @var string */
    protected $libraryClass;

    /** @var string|array */
    protected $files;

    /** @var null|string */
    protected $destination = null;

    /** @var null|string */
    protected $name = null;

    /** @var bool */
    protected $delete = false;

    /** @var array */
    protected $extract_files = [];

    /** @var string */
    protected $pattern;

    /**
     * Compression constructor.
     *
     * @param string|array $files
     * @param null|string  $destination
     * @param null|string  $name
     * @param bool         $delete
     * @param array        $extractFiles
     * @param null|string  $pattern
     */
    public function __construct(
        $files,
        $destination = null,
        $name = null,
        $delete = false,
        $extractFiles = [],
        $pattern = null
    )
    {
        $default_lib = config('kompressor.library');
        $libraryName = title_case($default_lib);
        $libraryClass = __NAMESPACE__.'\\Libraries\\'.$libraryName;

        $this->validateLibrary($libraryClass, $libraryName);

        $this->pattern = $pattern ?? config('kompressor.glob_dir_pattern', '*');
        $this->library = $libraryName;
        $this->libraryClass = $libraryClass;
        $this->files = $files;
        $this->destination = $destination;
        $this->name = $name;
        $this->delete = $delete;
        $this->extract_files = $extractFiles;
    }

    /**
     * @return mixed
     */
    public function getLibrary()
    {
        /** @See \samkitano\Kompressor\CompressionContract */
        call_user_func("$this->libraryClass::meetsRequirements");

        return new $this->libraryClass(
            $this->library,
            $this->files,
            $this->destination,
            $this->name,
            $this->delete,
            $this->extract_files,
            $this->pattern
        );
    }

    /**
     * @param $class
     * @param $library
     *
     * @return void
     * @throws \samkitano\Kompressor\Exceptions\KompressorException
     */
    protected function validateLibrary($class, $library): void
    {
        if (! class_exists($class)) {
            throw new KompressorException("Library '$library' not supported.");
        }
    }

    /**
     * @return array|string
     */
    public function getFiles()
    {
        return $this->files;
    }
}
