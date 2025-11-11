<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'category_id', 
        'subcategory_id', 
        'title', 
        'description', 
        'location',
        'coordinates',
        'date_damage',
        'affected_area',
        'criticallity',
        'status',
        'pdf_report'
    ];

    protected $dates = ['date_damage'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    //Aqui faltaran añadir mas
}
