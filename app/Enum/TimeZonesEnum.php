<?php

namespace App\Enum;

use App\Traits\EnumValues;

enum TimeZonesEnum: string
{
    use EnumValues;
    case CET = 'CET';
    case CST = 'CST';
    case GMT_1 = 'GMT+1';

}
