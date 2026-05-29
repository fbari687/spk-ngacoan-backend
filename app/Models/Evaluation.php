<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['supplier_id', 'criterion_id', 'actual_value'])]
class Evaluation extends Model
{
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class, 'criterion_id', 'id');
    }
}
