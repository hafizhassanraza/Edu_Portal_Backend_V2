<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmployeeQualification ; // Assuming you have a Qualification model

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employees';
    

    protected $fillable = [
        'full_name',
        'father_name',
        'gender',
        'dob',
        'martial_status',
        'cnic',
        'address',
        'blood_group',
        'health_profile',
        'photo',
        'email',
        'phone',
        'password',
        'status',
    ];

    protected $attributes = [
        'status' => 'pending', // Default status
        'password' => '12345678', // Default status
    ];

    public function qualification()
    {
        return $this->hasOne(EmployeeQualification::class, 'employee_id', 'id');// Assuming the table name is 'employee_qualifications'. and the foreign key in the qualifications table is 'employee_id'.                                                                        
    }



}
