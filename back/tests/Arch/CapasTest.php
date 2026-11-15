<?php

arch('el dominio es PHP puro')
    ->expect('App\Dominio')
    ->not->toUse(['Illuminate', 'Laravel', 'App\Aplicacion', 'App\Infraestructura']);

arch('la aplicación no conoce el framework ni la infraestructura')
    ->expect('App\Aplicacion')
    ->not->toUse(['Illuminate', 'Laravel', 'App\Infraestructura']);

arch('los casos de uso y sus DTO son finales')
    ->expect('App\Aplicacion\Inventario')->classes()->toBeFinal()
    ->and('App\Aplicacion\Prestamos')->classes()->toBeFinal()
    ->and('App\Aplicacion\Administracion')->classes()->toBeFinal();

arch('las entidades del dominio no extienden modelos de Eloquent')
    ->expect('App\Dominio')
    ->not->toExtend('Illuminate\Database\Eloquent\Model');

arch('no hay depuración olvidada')->preset()->php();
