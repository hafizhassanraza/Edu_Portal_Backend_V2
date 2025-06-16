<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'student_id',
        'class_id',
        'hostel_assign',
        'transport_assign',
        'previous_school'
    ];


    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function myClass()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }


}
