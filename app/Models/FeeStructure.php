<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $table = 'fee_structures';
    protected $fillable = [
        'class_id',
        'admission_fee',
        'tuition_fee',
        'hostel_fee',
        'exam_fee',
        'transport_fee',
        'library_fee',
        'lab_fee',
        'medical_fee',
        'sports_fee',
        'utility_charges',
        'other'
    ];
    protected $casts = [
        'admission_fee' => 'integer',
        'tuition_fee' => 'integer',
        'hostel_fee' => 'integer',
        'exam_fee' => 'integer',
        'transport_fee' => 'integer',
        'library_fee' => 'integer',
        'lab_fee' => 'integer',
        'medical_fee' => 'integer',
        'sports_fee' => 'integer',
        'utility_charges' => 'integer',
        'other' => 'integer'
    ];
    protected $attributes = [
        'admission_fee' => 0,
        'tuition_fee' => 0,
        'hostel_fee' => 0,
        'exam_fee' => 0,
        'transport_fee' => 0,
        'library_fee' => 0,
        'lab_fee' => 0,
        'medical_fee' => 0,
        'sports_fee' => 0,
        'utility_charges' => 0,
        'other' => 0
    ];
    
    //Relationships
    public function myClass()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }


}
