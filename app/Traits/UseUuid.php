<?php

namespace App\Traits;

trait UseUuid {
    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->attributes['id'] = $this->generateUuid();
    }

    public function generateUuid()
    {
        return (string) str()->uuid();
    }
}