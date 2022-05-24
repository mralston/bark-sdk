<?php

declare(strict_types=1);

namespace Mralston\Bark\Entities;

class Buyer extends Record
{
    public ?string $name;
    public ?string $short_name;
    public ?string $email;
    public ?string $telephone;
    public ?string $telephone_formatted;
}
