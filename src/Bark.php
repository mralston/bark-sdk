<?php

declare(strict_types=1);

namespace Mralston\Bark;

use Carbon\Carbon;
use Exception;

class Bark extends Record
{
    public ?int $id;
    public ?Carbon $createdAt;
    public ?Carbon $updatedAt;
    public ?object $display;
    public ?object $entities;
    public ?object $metadata;
    public ?object $interactions;
    public ?int $credits_required;
    public ?int $purchased_count;
    public ?int $purchase_cap;

    protected $dates = [
        'createdAt',
        'updatedAt',
    ];
}
