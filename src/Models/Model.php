<?php

declare(strict_types=1);

namespace Mralston\Bark\Models;

use Mralston\Bark\Client;
use Mralston\Bark\Traits\HasAttributes;
use Mralston\Bark\Traits\TransformsBarkDates;

class Model
{
    use HasAttributes;
    use TransformsBarkDates;

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
