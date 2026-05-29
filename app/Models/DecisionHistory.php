<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'calculated_at'])]
class DecisionHistory extends Model
{
    public function rankings()
    {
        return $this->hasMany(Ranking::class, 'decision_history_id', 'id');
    }
}
