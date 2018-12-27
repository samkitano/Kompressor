<?php

namespace samkitano\Kompressor;

interface MethodContract
{
    /**
     * So we can have all data provided by the Method Class
     * available in the library class.
     *
     * @return array
     */
    public function getData(): array;
}
