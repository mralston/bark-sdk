<?php

declare(strict_types=1);

namespace Mralston\Bark\Entities;

use Carbon\Carbon;

class Message extends Record
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
