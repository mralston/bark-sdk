<?php

declare(strict_types=1);

namespace Mralston\Bark\Entities;

use Carbon\Carbon;

class Bark extends Record
{
    public ?int $id;
    public ?Carbon $created_at;
    public ?Carbon $updated_at;
    public ?Display $display;
    public array $entities = [];
    public ?Metadata $metadata;
    public ?array $interactions;
    public ?int $credits_required;
    public ?int $purchased_count;
    public ?int $purchase_cap;

    protected array $dates = [
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'display' => Display::class,
        'interactions' => 'array',
        'metadata' => Metadata::class,
    ];

    protected array $transforms = [
        'created_at' => 'BarkDate',
        'updated_at' => 'BarkDate',
        'entities' => 'BarkEntities',
    ];

    protected function transformBarkEntities(object $entities): array
    {
        $output = [];

        foreach ($entities as $key => $value) {
            if ($key == 'buyer') {
                $output[$key] = new Buyer($value);
            }
        }

        return $output;
    }
}
