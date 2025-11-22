<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;  //esto se ha añadido
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active'
    ];

    public function subcategories() {
        return $this->hasMany(Subcategory::class);   //relacion 1:N
    }

    public function reports() {
        return $this->hasMany(Report::class);   //relacion 1:N
    }
}
