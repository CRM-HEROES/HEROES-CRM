<?php

namespace App\Support\Import;

class FastXlsxSheet
{
    protected $reader;
    protected $name;
    protected $internalPath;

    public function __construct(FastXlsxReader $reader, string $name, string $internalPath)
    {
        $this->reader = $reader;
        $this->name = $name;
        $this->internalPath = $internalPath;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRowIterator()
    {
        return $this->reader->readRows($this->internalPath);
    }
}
