<?php

namespace App\Infraestructura\Proveedores;

use App\Aplicacion\Puertos\CacheCapacidad;
use App\Aplicacion\Puertos\CachePermisos;
use App\Aplicacion\Puertos\HashClaves;
use App\Aplicacion\Puertos\Reloj;
use App\Aplicacion\Puertos\Transaccion;
use App\Dominio\Repositorios\RepositorioBiblioteca;
use App\Dominio\Repositorios\RepositorioConfiguracion;
use App\Dominio\Repositorios\RepositorioEjemplares;
use App\Dominio\Repositorios\RepositorioLectores;
use App\Dominio\Repositorios\RepositorioLibros;
use App\Dominio\Repositorios\RepositorioMultas;
use App\Dominio\Repositorios\RepositorioPrestamos;
use App\Dominio\Repositorios\RepositorioUsuarios;
use App\Infraestructura\Persistencia\Repositorios\EloquentRepositorioBiblioteca;
use App\Infraestructura\Persistencia\Repositorios\EloquentRepositorioConfiguracion;
use App\Infraestructura\Persistencia\Repositorios\EloquentRepositorioEjemplares;
use App\Infraestructura\Persistencia\Repositorios\EloquentRepositorioLectores;
use App\Infraestructura\Persistencia\Repositorios\EloquentRepositorioLibros;
use App\Infraestructura\Persistencia\Repositorios\EloquentRepositorioMultas;
use App\Infraestructura\Persistencia\Repositorios\EloquentRepositorioPrestamos;
use App\Infraestructura\Persistencia\Repositorios\EloquentRepositorioUsuarios;
use App\Infraestructura\Servicios\CacheCapacidadRedis;
use App\Infraestructura\Servicios\CachePermisosRedis;
use App\Infraestructura\Servicios\HashClavesLaravel;
use App\Infraestructura\Servicios\RelojSistema;
use App\Infraestructura\Servicios\TransaccionLaravel;
use Illuminate\Support\ServiceProvider;

/** Enlaza las interfaces del Dominio y los puertos de Aplicación con sus implementaciones (DA-07). */
class DominioServiceProvider extends ServiceProvider
{
    public array $bindings = [
        RepositorioBiblioteca::class => EloquentRepositorioBiblioteca::class,
        RepositorioConfiguracion::class => EloquentRepositorioConfiguracion::class,
        RepositorioEjemplares::class => EloquentRepositorioEjemplares::class,
        RepositorioLectores::class => EloquentRepositorioLectores::class,
        RepositorioLibros::class => EloquentRepositorioLibros::class,
        RepositorioMultas::class => EloquentRepositorioMultas::class,
        RepositorioPrestamos::class => EloquentRepositorioPrestamos::class,
        RepositorioUsuarios::class => EloquentRepositorioUsuarios::class,
        Transaccion::class => TransaccionLaravel::class,
        Reloj::class => RelojSistema::class,
        CacheCapacidad::class => CacheCapacidadRedis::class,
        CachePermisos::class => CachePermisosRedis::class,
        HashClaves::class => HashClavesLaravel::class,
    ];
}
