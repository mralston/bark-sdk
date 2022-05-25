<?php

declare(strict_types=1);

namespace Mralston\Bark\Models;

use Carbon\Carbon;

class Message extends Model
{
    public ?string $type;
    public ?string $label;
    public ?Carbon $time;
    public ?bool $is_read;
    public ?string $sender;

    protected array $dates = [
        'time',
    ];

    protected array $casts = [
        'is_read' => 'bool',
    ];
}
