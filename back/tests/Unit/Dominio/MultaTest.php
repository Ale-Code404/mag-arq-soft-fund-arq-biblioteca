<?php

use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Penalizaciones\Multa;

it('marca_pagada_una_sola_vez', function () {
    $m = Multa::pendiente(2000, 2, 1000);
    $m->marcarPagada(fecha('2026-11-15'));

    expect($m->estaPendiente())->toBeFalse()
        ->and(fn () => $m->marcarPagada(fecha('2026-11-16')))->toThrow(ReglaNegocioException::class, 'La multa ya fue pagada.');
});
