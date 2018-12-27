<?php

namespace samkitano\Kompressor;

use Illuminate\Support\Facades\File;

class Extract implements MethodContract
{
    /** @var string */
    protected $source_archive;

    /** @var array */
    protected $extract_files;

    /** @var string */
    protected $destination_dir;

    /**
     * Extract constructor.
     *
     * @param string      $archive
     * @param string|null $destination
     * @param array       $extractFiles
     */
    public function __construct(string $archive, $destination, array $extractFiles)
    {
        $this->source_archive = $archive;
        $this->destination_dir = $this->destinationDir($destination);
        $this->extract_files = $extractFiles;
    }

    /**
     * So we can have all data provided by the Method Class
     * available in the library class.
     *
     * @return array
     */
    public function getData(): array
    {
        $res = [
            'source_archive' => $this->source_archive,
            'destination_dir' => $this->destination_dir,
        ];

        if (count($this->extract_files)) {
            $res['extract_files'] = $this->extract_files;
        }

        return $res;
    }

    /**
     * @param string|null $destination
     *
     * @return string
     */
    protected function destinationDir($destination): string
    {
        return $destination ?? File::dirname($this->source_archive);
    }
}
