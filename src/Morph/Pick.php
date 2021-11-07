<?php

declare(strict_types=1);

namespace Morph;

class Pick extends MorphBase
{
    /**
     * @var string|int
     */
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function __invoke($value)
    {
        return $value[$this->key] ?? null;
    }
}
