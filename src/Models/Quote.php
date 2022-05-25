<?php

declare(strict_types=1);

namespace Mralston\Bark\Models;

use Carbon\Carbon;

class Quote extends Model
{
    public ?string $type;
    public ?float $value;
    public ?string $detail;
}
