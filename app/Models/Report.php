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
        'ip',
        'title', 
        'background', 
        'community',
        'province',
        'locality',
        'petitioner_id',
        'petitioner_other',
        'urgency',
        'date_petition',
        'date_damage',
        'status',
        'assigned',
        'assigned_to',
        'pdf_report',
    ];

    protected $casts = [
        'date_petition' => 'date',
        'date_damage' => 'date',
        'assigned' => 'boolean',
    ];

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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function petitioner()
    {
        return $this->belongsTo(Petitioner::class);
    }

    //Aqui faltaran añadir mas
}
