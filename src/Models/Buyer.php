<?php

declare(strict_types=1);

namespace Mralston\Bark\Models;

class Buyer extends Model
{
    public ?string $name;
    public ?string $short_name;
    public ?string $email;
    public ?string $telephone;
    public ?string $telephone_formatted;
}
