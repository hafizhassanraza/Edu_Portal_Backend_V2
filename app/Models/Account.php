<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    
    protected $table = 'accounts';
    protected $fillable = [
        'student_id',
        'fine',
        'dues',
        'discount_type',
        'discount_amount',
        'discount_percent',
        'status',
        'remarks'
    ];
    protected $casts = [
        'fine' => 'integer',
        'dues' => 'integer',
        'discount_amount' => 'integer',
        'discount_percent' => 'float',
    ];
    protected $attributes = [
        'fine' => 0,
        'dues' => 0,
        'discount_type' => 'none',
        'discount_amount' => 0,
        'discount_percent' => 0.0,
        'status' => 'active',
    ];

    //relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }



}
