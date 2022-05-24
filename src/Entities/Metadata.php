<?php

declare(strict_types=1);

namespace Mralston\Bark\Entities;

class Metadata extends Record
{
    public ?array $images = []  ;
    public ?Country $country;
    public ?Category $category;
    public ?array $questions;
    public ?Location $location;

    protected array $casts = [
        'country' => Country::class,
        'category' => Category::class,
        'location' => Location::class,
    ];

    protected array $transforms = [
        'questions' => 'BarkQuestions',
    ];

    protected function transformBarkQuestions($questions): array
    {
        $output = [];

        foreach ($questions->data as $question) {
        $output[] = new Question($question);
        }

        return $output;
    }
}
