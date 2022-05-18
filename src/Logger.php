<?php

namespace Mralston\Bark;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        file_put_contents(
            '/tmp/bark.log',
            date('Y-m-d H:i:s') . ' ' .
                $level . ' ' .
                $message . "\n\n"
        );

//        print_r($message);
    }
}