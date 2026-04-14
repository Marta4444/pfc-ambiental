<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petitioner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

}