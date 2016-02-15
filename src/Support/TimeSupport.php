<?php

namespace Kraken\Support;

abstract class TimeSupport
{
    /**
     * @return float
     */
    public static function now()
    {
        return round(microtime(true)*1000);
    }
}