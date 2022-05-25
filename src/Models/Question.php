<?php

declare(strict_types=1);

namespace Mralston\Bark\Models;

class Question extends Model
{
    public ?string $question;
    public ?string $type;
    public ?array $possible_answers = [];
    public ?bool $is_required;
    public ?bool $is_custom_answer;
    public ?string $answer_type;
    public ?array $answer;

    protected array $casts = [
        'answer' => 'array'
    ];
}
