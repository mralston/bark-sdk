<?php

declare(strict_types=1);

namespace Mralston\Bark;

use Mralston\Bark\Traits\HasAttributes;

class Record
{
    use HasAttributes;

    protected ?Client $client;

    public function __construct($attributes, ?Client $client = null)
    {
        $this->fill($attributes);

        $this->client = $client;
    }

    public static function make($attributes, ?Client $client = null)
    {
        return new static($attributes, $client);
    }
}
