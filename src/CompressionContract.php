<?php

namespace samkitano\Kompressor;

interface CompressionContract
{
    /**
     * Creates a new archive from given file, array of files, or directory
     *
     * @return string
     */
    public function compress(): string;

    /**
     * Extracts the contents from the archive
     *
     * @return string
     */
    public function extract(): string;

    /**
     * Adds a file or array of files to an existing archive
     *
     * @return string
     */
    public function add(): string;

    /**
     * Removes an archived file, or array of archived files from the archive
     *
     * @return string
     */
    public function remove(): string;

    /**
     * Returns the contents (archived files) of the archive
     *
     * @return array
     */
    public function read(): array;

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
    public static function meetsRequirements(): bool;
}
