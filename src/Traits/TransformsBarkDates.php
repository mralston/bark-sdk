<?php

declare(strict_types=1);

namespace Mralston\Bark\Traits;

use Carbon\Carbon;

trait TransformsBarkDates
{
    protected function transformBarkDate($date): Carbon
    {
        return Carbon::parse($date->date_utc);
    }
}
