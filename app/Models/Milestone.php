<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $guarded = [];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
