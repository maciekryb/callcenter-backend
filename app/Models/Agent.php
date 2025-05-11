<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;


    public static function create(string $name, string $email): self
    {
        $agent = new self();
        $agent->name = $name;
        $agent->email = $email;
        $agent->save();

        return $agent;
    }

    public function queues()
    {
        return $this->belongsToMany(Queue::class, 'agent_queues')->withPivot('efficiency')->withTimestamps();
    }
}
