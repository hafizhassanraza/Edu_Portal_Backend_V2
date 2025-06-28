<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incharge extends Model
{
    use HasFactory;

    protected $table = 'incharges';
    protected $fillable = [
        'class_id',
        'section_id',
        'employee_id',
    ];


    public function class()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    // Assuming the employee is a teacher assigned to the section
    

}
