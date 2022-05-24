<?php

declare(strict_types=1);

namespace Mralston\Bark\Entities;

class Display extends Record
{
    public ?string $html;
    public ?string $text;
    public ?string $url;
}
