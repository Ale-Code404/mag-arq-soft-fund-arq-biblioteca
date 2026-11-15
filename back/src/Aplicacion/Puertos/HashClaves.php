<?php

declare(strict_types=1);

namespace App\Aplicacion\Puertos;

interface HashClaves
{
    public function hashear(string $clave): string;
}
