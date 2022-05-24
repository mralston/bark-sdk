<?php

declare(strict_types=1);

namespace Mralston\Bark\Entities;

class Question extends Record
{
    public ?string $question;
    public ?string $type;
    public ?array $possible_answers = [];
    public ?bool $is_required;
    public ?bool $is_custom_answer;
    public ?string $answer_type;
    public ?string $answer;
}
