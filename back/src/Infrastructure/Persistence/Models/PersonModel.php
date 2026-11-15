<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class PersonModel extends Model
{
    public const READER = 'READER';

    public const STAFF = 'STAFF';

    protected $table = 'people';

    protected $guarded = [];
}
