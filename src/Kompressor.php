<?php

namespace samkitano\Kompressor;

class Kompressor
{
    /**
     * @param string|array $files
     * @param string|null  $destination
     * @param string|null  $name
     * @param bool         $delete
     * @param string|null  $pattern
     *
     * @return string
     */
    function compress($files, $destination = null, $name = null, $delete = false, $pattern = null): string
    {
        $kompressor = new Compression(
            $files,
            $destination,
            $name,
            $delete,
            [],
            $pattern
        );

        $library = $kompressor->getLibrary();

        return $library->compress();
    }

    /**
     * @param string      $source
     * @param string|null $destination
     * @param bool        $delete
     * @param array       $extractFiles
     *
     * @return string
     */
    function extract(string $source, $destination = null, $delete = false, $extractFiles = []): string
    {
        $kompressor = new Compression(
            $source,
            $destination,
            null,
            $delete,
            $extractFiles
        );

        $library = $kompressor->getLibrary();

        return $library->extract();
    }

    /**
     * @param string        $source
     * @param string|array  $files
     * @param bool          $delete
     *
     * @return string
     */
    function add(string $source, $files, $delete = false): string
    {
        $kompressor = new Compression($source, $files, null, $delete);
        $library = $kompressor->getLibrary();

        return $library->add();
    }

    /**
     * @param string       $source
     * @param string|array $files
     *
     * @return string
     */
    function remove(string $source, $files): string
    {
        $kompressor = new Compression($source, $files, null);
        $library = $kompressor->getLibrary();

        return $library->remove();
    }

    /**
     * @param string $source
     *
     * @return array
     */
    function read(string $source): array
    {
        $kompressor = new Compression($source);
        $library = $kompressor->getLibrary();

        return $library->read();
    }
}
