<?php

namespace Mralston\Bark\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected $message = 'Not Found';
}