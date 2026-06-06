<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'address', 'phone', 'latitude', 'longitude'])]
class Supplier extends Model
{
    use SoftDeletes;
}
