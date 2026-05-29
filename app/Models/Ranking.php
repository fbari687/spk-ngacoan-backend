<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['decision_history_id', 'supplier_id', 'appraisal_score', 'rank'])]
class Ranking extends Model
{
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function decisionHistory()
    {
        return $this->belongsTo(DecisionHistory::class, 'decision_history_id', 'id');
    }
}
