<?php

declare(strict_types=1);

namespace Mralston\Bark\Models;

class City extends Model
{
    public ?int $id;
    public ?string $name;
    public ?float $latitude;
    public ?float $longitude;
    public ?int $country_id;
}
