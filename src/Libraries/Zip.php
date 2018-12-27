<?php

namespace samkitano\Kompressor\Libraries;

use ZipArchive;
use samkitano\Kompressor\BaseCompression;
use samkitano\Kompressor\CompressionContract;
use samkitano\Kompressor\Exceptions\KompressorException;

class Zip extends BaseCompression implements CompressionContract
{
    /** @var int */
    protected $operation = ZipArchive::CREATE;

    /**
     * Creates a new archive from given file, array of files, or directory
     *
     * @return string
     */
    function compress(): string
    {
        $zip = new ZipArchive();
        $open_file = $this->data['destination_dir'].DIRECTORY_SEPARATOR.$this->data['destination_file_name'];

        $zip->open($open_file, $this->operation);

        foreach ($this->data['source_files'] as $datum) {
            $local_name = $this->keep_paths
                ? null
                : basename($datum);

            $zip->addFile($datum, $local_name);
        }

        $zip->close();

        $this->processDeletion($this->data['source_files']);

        return $this->getResponse();
    }

    /**
     * Extract given files from an archive
     *
     * @return string
     */
    function extract(): string
    {
        $zip = new ZipArchive();

        $zip->open($this->data['source_archive']);

        $this->setNumFiles($zip->numFiles);

        if (isset($this->data['extract_files'])) {
            $zip->extractTo($this->data['destination_dir'], $this->data['extract_files']);
        } else {
            $zip->extractTo($this->data['destination_dir']);
        }

        $zip->close();

        $this->processDeletion($this->data['source_archive']);

        return $this->getResponse();
    }

    /**
     * Adds a file or array of files to an existing archive
     *
     * @return string
     */
    function add(): string
    {
        $overwritten = 0;
        //$this->operation = ZipArchive::CREATE | ZipArchive::OVERWRITE;

        foreach ($this->data['files'] as $file) {
            $filename = basename($file);
            $zip = new ZipArchive();

            $zip->open($this->data['source'], $this->operation);

            if ($zip->locateName($filename) !== false) {
                $overwritten++;
            }

            $zip->addFile($file, $filename);

            $this->setNumFiles(++$this->numFiles);

            $zip->close();
        }

        $this->processDeletion($this->data['files']);


        return $this->getResponse();
    }

    /**
     * Removes an archived file, or array of archived files from the archive
     *
     * @return string
     */
    function remove(): string
    {
        foreach ($this->data['files'] as $file) {
            $zip = new ZipArchive();

            $zip->open($this->data['source'], $this->operation);
            $zip->deleteName($file);

            $this->setNumFiles(++$this->numFiles);

            $zip->close();
        }

        return $this->getResponse();
    }

    /**
     * Returns the contents (archived files) of the archive
     *
     * @return array
     */
    function read(): array
    {
        $files = [];
        $zip = new ZipArchive();

        if ($zip->open($this->data['source'])) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $files[] = $zip->getNameIndex($i);
            }

            $zip->close();
        }

        return $files;
    }

    /**
     * By implementing this static method on each and every library class,
     * we make sure our libraries meet any particular requirements. The
     * function must throw a KompressorException if requirements are
     * not met, or simply return a boolean true value if we don't
     * actually have any particular requirement at all to meet.
     *
     * @return bool
     * @throws \samkitano\Kompressor\Exceptions\KompressorException
     */
    static function meetsRequirements(): bool
    {
        if (! extension_loaded('zip')) {
            throw new KompressorException('PHP - Zip extension is not installed.');
        }

        return true;
    }
}
