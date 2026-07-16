<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiEvaluatorWeight extends Model
{
    protected $fillable = ['job_level', 'slot', 'weight'];
}
