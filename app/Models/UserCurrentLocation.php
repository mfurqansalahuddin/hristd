<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCurrentLocation extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['user_id', 'lat', 'long', 'last_updated_at'];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
