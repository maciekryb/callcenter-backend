<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    const TYPE_GENERAL = "general";
    const TYPE_SALES = "sales";
    const TYPE_RESALES = "resales";
    const TYPE_SUPPORT = "technical_support";

    const validTypes = [
        self::TYPE_GENERAL,
        self::TYPE_SALES,
        self::TYPE_RESALES,
        self::TYPE_SUPPORT
    ];

    public function agents()
    {
        return $this->belongsToMany(Agent::class)->withPivot('efficiency')->withTimestamps();
    }
}
