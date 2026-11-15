<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Administracion\Dto\AccionUsuario;
use App\Aplicacion\Administracion\Dto\GestionarUsuariosEntrada;
use App\Aplicacion\Administracion\GestionarUsuarios;
use App\Infraestructura\Http\Solicitudes\AsignarRolesSolicitud;
use App\Infraestructura\Http\Solicitudes\CrearUsuarioSolicitud;
use App\Infraestructura\Http\Solicitudes\EditarUsuarioSolicitud;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controlador
{
    public function store(CrearUsuarioSolicitud $request, GestionarUsuarios $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new GestionarUsuariosEntrada(
            accion: AccionUsuario::CREAR,
            ejecutadoPor: $this->usuarioId($request),
            login: $request->input('login'),
            clave: $request->input('clave'),
            identificacion: $request->input('identificacion'),
            nombre: $request->input('nombre'),
            cargo: $request->input('cargo'),
            roles: $request->input('roles'),
        )), 201);
    }

    public function update(int $id, EditarUsuarioSolicitud $request, GestionarUsuarios $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new GestionarUsuariosEntrada(
            accion: AccionUsuario::EDITAR,
            ejecutadoPor: $this->usuarioId($request),
            usuarioId: $id,
            login: $request->input('login'),
            clave: $request->input('clave'),
            nombre: $request->input('nombre'),
            cargo: $request->input('cargo'),
            activo: $request->has('activo') ? $request->boolean('activo') : null,
        )));
    }

    public function roles(int $id, AsignarRolesSolicitud $request, GestionarUsuarios $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new GestionarUsuariosEntrada(
            accion: AccionUsuario::ASIGNAR_ROLES,
            ejecutadoPor: $this->usuarioId($request),
            usuarioId: $id,
            roles: $request->input('roles'),
        )));
    }
}
