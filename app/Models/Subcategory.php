<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;  //se añade
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'active'
    ];

    public function category() {

        return $this->belongsTo(Category::class);
    }

    public function reports() {
        
        return $this->hasMany(Report::class);
    }

}
