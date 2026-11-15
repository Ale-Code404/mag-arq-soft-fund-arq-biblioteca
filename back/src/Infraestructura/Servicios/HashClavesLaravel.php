<?php

namespace App\Infraestructura\Servicios;

use App\Aplicacion\Puertos\HashClaves;
use Illuminate\Support\Facades\Hash;

class HashClavesLaravel implements HashClaves
{
    public function hashear(string $clave): string
    {
        return Hash::make($clave);
    }
}
