<?php

use App\Dominio\Excepciones\ReglaNegocioException;
use App\Dominio\Personas\Funcionario;
use App\Dominio\Personas\Lector;
use App\Dominio\Personas\Persona;
use App\Dominio\Seguridad\Permiso;
use App\Dominio\Seguridad\Rol;
use App\Dominio\Seguridad\Usuario;

it('lector_y_funcionario_heredan_de_persona', function () {
    expect(new Lector('1', 'Ana'))->toBeInstanceOf(Persona::class)
        ->and(new Funcionario('2', 'Luis', 'Bibliotecario'))->toBeInstanceOf(Persona::class);
});

it('usuario_tiene_permiso_por_sus_roles_solo_si_esta_activo', function () {
    $u = new Usuario(1, 'director', 'hash', true, new Funcionario('2', 'Luis', 'Director'), [
        new Rol('Director', [Permiso::CAPACIDAD_CONSULTAR, Permiso::PRESTAMO_CONSULTAR]),
    ]);

    expect($u->tienePermiso(Permiso::CAPACIDAD_CONSULTAR))->toBeTrue()
        ->and($u->tienePermiso(Permiso::USUARIO_GESTIONAR))->toBeFalse();

    $u->desactivar();
    expect($u->tienePermiso(Permiso::CAPACIDAD_CONSULTAR))->toBeFalse();
});

it('configuracion_exige_valores_positivos', function () {
    configuracion(holguraCm: 0);
})->throws(ReglaNegocioException::class);
