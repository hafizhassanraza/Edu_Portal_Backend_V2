<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';
    protected $fillable = [
        'full_name',
        'father_name',
        'gender',
        'dob',
        'b_form',
        'address',
        'blood_group',
        'health_profile',
        'photo',
        'email',
        'password',
        'status',
    ];
    protected $attributes = [
        'status' => 'pending', // Default status
        'password' => '12345678', // Default status
    ];

    // Relationships
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'enrollments', 'student_id', 'section_id');
    }
    public function classes()
    {
        return $this->belongsToMany(MyClass::class, 'enrollments', 'student_id', 'class_id');
    }
    public function enrollment()
    {
        return $this->hasOne(Enrollment::class, 'student_id');
    }
    public function guardian()
    {
        return $this->hasOne(Guardian::class, 'student_id');
    }
    public function account()
    {
        return $this->hasOne(Account::class, 'student_id');
    }
    public function feeSlips()
    {
        return $this->hasMany(FeeSlip::class, 'student_id');
    }

    public function extras()
    {
        return $this->hasOne(Extra::class, 'student_id');
    }

    public function admissionSlips()
    {
        return $this->hasMany(FeeSlip::class, 'student_id')->where('type', 'admission');
    }

    public function resultRecords()
    {
        return $this->hasMany(ResultRecord::class, 'student_id');
    }





    

}
