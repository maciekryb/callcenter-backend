<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;


    public static function getByNameOrFail(string $name): Queue
    {
        return self::where('name', $name)->firstOrFail();
    }

    public function agents()
    {
        return $this->belongsToMany(Agent::class)->withPivot('efficiency')->withTimestamps();
    }
}
