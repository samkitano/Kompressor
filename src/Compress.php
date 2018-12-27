<?php

namespace samkitano\Kompressor;

use Illuminate\Support\Facades\File;

class Compress implements MethodContract
{
    /** @var mixed|string */
    protected $source_dir;

    /** @var array */
    protected $source_files;

    /** @var mixed|string */
    protected $destination_dir;

    /** @var string */
    protected $destination_file_name;

    /** @var bool */
    protected $source_is_dir;

    /** @var string */
    protected $search_pattern;

    /**
     * Create constructor.
     *
     * @param string|array $files
     * @param string|null  $destination
     * @param string|null  $name
     * @param bool         $sourceIsDirectory
     * @param string       $globPattern
     * @param string       $extension
     * @param string       $configPath
     */
    function __construct($files, $destination, $name, $sourceIsDirectory, $globPattern, $extension, $configPath)
    {
        $this->search_pattern = $globPattern;
        $this->source_is_dir = $sourceIsDirectory;
        $this->source_dir = $this->sourceDir($files);
        $this->source_files = $this->parseFiles($files);
        $this->destination_dir = $this->destinationDir($destination, $configPath);
        $this->destination_file_name = $this->destinationFileName($name).$extension;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [
            'source_dir' => $this->source_dir,
            'source_files' => $this->source_files,
            'destination_dir' => $this->destination_dir,
            'destination_file_name' => $this->destination_file_name,
        ];
    }

    /**
     * @param string|array $files
     *
     * @return string
     */
    protected function sourceDir($files): string
    {
        $files = (array) $files;

        return $this->source_is_dir
            ? $files[0]
            : File::dirname($files[0]);
    }

    /**
     * @param string|null $destination
     * @param string|null $configPath
     *
     * @return string
     */
    protected function destinationDir($destination, $configPath): string
    {
        return $destination ?? $configPath ?? $this->source_dir;
    }

    /**
     * @param string|null $name
     *
     * @return string
     */
    protected function destinationFileName($name): string
    {
        if (isset($name)) {
            return $name; // Passed name always has precedence
        }

        if ($this->source_is_dir) {
            return basename($this->source_dir);
        } else {
            return $this->isSingleFile()
                ? pathinfo($this->source_files[0], PATHINFO_FILENAME)
                : basename($this->source_dir);
        }
    }

    /**
     * @return bool
     */
    protected function isSingleFile(): bool
    {
        return count($this->source_files) === 1;
    }

    /**
     * @param string|array $files
     *
     * @return array
     */
    protected function parseFiles($files): array
    {
        if ($this->source_is_dir) {
            return glob($this->source_dir.DIRECTORY_SEPARATOR.$this->search_pattern);
        }

        return (array) $files;
    }
}
