<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'class_id',
        'month',
        'year',
        'type'
    ];
    public function myClass()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }

    public function feeSlips()
    {
        return $this->hasMany(FeeSlip::class, 'fee_id');
    }
    
   


}
