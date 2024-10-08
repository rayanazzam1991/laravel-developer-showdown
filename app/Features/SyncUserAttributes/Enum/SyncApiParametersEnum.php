<?php

namespace App\Features\SyncUserAttributes\Enum;

enum SyncApiParametersEnum : int
{

    // RPH = Request Per Hour
    // CPH = Call Per Hour
    case MAX_BATCH_RPH = 50;
    case MAX_INDIVIDUAL_RPH = 3600;
    case MAX_RECORDS_PER_BATCH_REQUEST = 1000;
}
