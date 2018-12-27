<?php

namespace samkitano\Kompressor;

class Read implements MethodContract
{
    /** @var string */
    protected $source;

    /**
     * Read constructor.
     *
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->source = $source;
    }

    /**
     * So we can have all data provided by the Method Class
     * available in the library class.
     *
     * @return array
     */
    public function getData(): array
    {
        return ['source' => $this->source];
    }
}
