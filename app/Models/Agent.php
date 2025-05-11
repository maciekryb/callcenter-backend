<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    public function queues()
    {
        return $this->belongsToMany(Queue::class)->withPivot('efficiency')->withTimestamps();
    }
}
