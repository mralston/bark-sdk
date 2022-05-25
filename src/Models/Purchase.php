<?php

declare(strict_types=1);

namespace Mralston\Bark\Models;

use Carbon\Carbon;

class Purchase extends Model
{
    public ?Bark $bark;
    public ?Carbon $created_at;
    public ?Quote $quote;
    public ?string $note;
    public ?Message $last_message;

    protected array $casts = [
        'bark' => Bark::class,
        'quote' => Quote::class,
        'last_message' => Message::class,
    ];

    protected array $transforms = [
        'created_at' => 'BarkDate',
    ];
}
