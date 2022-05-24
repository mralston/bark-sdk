<?php

declare(strict_types=1);

namespace Mralston\Bark\Entities;

class Category extends Record
{
    public ?int $id;
    public ?string $name;
}
