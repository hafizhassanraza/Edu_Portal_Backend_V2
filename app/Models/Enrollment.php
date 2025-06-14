<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'fee_slip_id',
        'reg_number',
        'enrollment_date',
        'academic_year',
        'previous_school',
        'remarks',
        'sibling_in',
        'status',
        'hostel_admission',
        'transport_admission'
    ];
     protected $attributes = [
        'sibling_in' => 0,
        'hostel_admission' => 0,
        'transport_admission' => 0,
        'status' => 'active',
    ];
   
    //Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function myClass()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
    public function AdmissionFeeSlip()
    {
        return $this->belongsTo(FeeSlip::class, 'fee_slip_id');
    }
    public function account()
    {
        return $this->hasOne(Account::class, 'student_id', 'student_id'); // linking to the student_id in the Account model
    }


}
