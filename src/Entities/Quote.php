<?php

declare(strict_types=1);

namespace Mralston\Bark\Entities;

use Carbon\Carbon;

class Quote extends Record
{
    public ?string $type;
    public ?float $value;
    public ?string $detail;
}
