<?php

namespace App\Infraestructura\Http\Controladores;

use App\Aplicacion\Administracion\ConfigurarParametros;
use App\Aplicacion\Administracion\ConsultarParametros;
use App\Aplicacion\Administracion\Dto\ConfigurarParametrosEntrada;
use App\Aplicacion\Compartido\ConsultaEntrada;
use App\Infraestructura\Http\Solicitudes\ConfigurarParametrosSolicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfiguracionController extends Controlador
{
    public function show(Request $request, ConsultarParametros $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new ConsultaEntrada($this->usuarioId($request))));
    }

    public function update(ConfigurarParametrosSolicitud $request, ConfigurarParametros $casoDeUso): JsonResponse
    {
        return $this->responder($casoDeUso->ejecutar(new ConfigurarParametrosEntrada(
            $request->integer('diasPrestamo'),
            $request->integer('diasRenovacion'),
            $request->integer('maxRenovaciones'),
            $request->integer('valorMultaDiaria'),
            $request->float('holguraCm'),
            $request->float('grosorPorDefectoCm'),
            $this->usuarioId($request),
        )));
    }
}
