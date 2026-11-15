<?php

arch('the domain is pure PHP')
    ->expect('App\Domain')
    ->not->toUse(['Illuminate', 'Laravel', 'App\Application', 'App\Infrastructure']);

arch('the application knows neither the framework nor the infrastructure')
    ->expect('App\Application')
    ->not->toUse(['Illuminate', 'Laravel', 'App\Infrastructure']);

arch('use cases and their DTOs are final')
    ->expect('App\Application\Inventory')->classes()->toBeFinal()
    ->and('App\Application\Loans')->classes()->toBeFinal()
    ->and('App\Application\Administration')->classes()->toBeFinal();

arch('domain entities do not extend Eloquent models')
    ->expect('App\Domain')
    ->not->toExtend('Illuminate\Database\Eloquent\Model');

arch('no leftover debugging')->preset()->php();
