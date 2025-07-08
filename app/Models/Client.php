<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';
    protected $guarded = [];

    public static function booted()
    {
        static::creating(function($client) {
            if(auth()->check()){
                $client->user_id = auth()->id();
            }
        });
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
