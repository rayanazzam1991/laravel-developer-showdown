<?php

namespace App\Features\SyncUserAttributes\Domain\Enum;

enum QueueDataStatusEnum: int
{
    case UN_SENT = 0;
    case PENDING = 1;
    case SENT = 2;
    case FAILED = 3;

}
