<?php

use Tests\RefreshDatabaseWithSeed;

pest()
    ->use(RefreshDatabaseWithSeed::class)
    ->in('./Feature');
