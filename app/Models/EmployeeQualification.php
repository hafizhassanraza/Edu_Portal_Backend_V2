<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeQualification extends Model
{
    use HasFactory;
    protected $table = 'employee_qualifications';


    protected $fillable = [
        'employee_id',
        'staff_id', 
        'role',
        'qualification',
        'institute',
        'year_of_passing',
        'joining_date',
        'specialization',
        'department',
        'experience',
        'grade',
        'remarks',
        'document_path',
    ];

    protected $casts = [
        'year_of_passing' => 'date',
        'joining_date' => 'date',
        'experience' => 'integer',
    ];
    /* protected $attributes = [
        'role' => 'employee', // Default role
        'experience' => 0, // Default experience
        'year_of_passing' => '2025-01-01',
    ]; */


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
