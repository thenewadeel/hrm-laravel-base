<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AuthServiceProvider::class,
    JetstreamServiceProvider::class,
];
