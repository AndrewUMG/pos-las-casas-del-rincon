<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class FamiliaModel extends Model
{
    protected $table = 'familias';

    protected $guarded = [];

    public $timestamps = false;
}
