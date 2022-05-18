<?php

namespace Mralston\Bark\Exceptions;

use Exception;

class InvalidSinceDateException extends Exception
{
    protected $message = 'Since Date must be one of 1h, today, yesterday, 3d, 7d or 2w.';
}