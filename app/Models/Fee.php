<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'class_id',
        'section_id',
        'month',
        'year',
        'type'
    ];




    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function myClass()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }

    public function feeSlips()
    {
        return $this->hasMany(FeeSlip::class, 'fee_id');
    }
    
   


}
