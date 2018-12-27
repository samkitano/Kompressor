<?php

namespace samkitano\Kompressor;

class Add implements MethodContract
{
    /** @var string */
    protected $source;

    /** @var array */
    protected $files;

    /**
     * Add constructor.
     *
     * @param string $source
     * @param array $files
     */
    function __construct(string $source, array $files)
    {
        $this->source = $source;
        $this->files = $files;
    }

    /**
     * So we can have all data provided by the Method Class
     * available in the library class.
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            'source' => $this->source,
            'files' => $this->files,
        ];
    }
}
