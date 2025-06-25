<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionSubject extends Model
{
    use HasFactory;

    protected $table = 'section_subjects';

    protected $fillable = [
        'class_id',
        'section_id',
        'subject_id',
        'employee_id',
    ];

    public function classes()
    {
        return $this->belongsToMany(MyClass::class, 'section_subjects', 'subject_id', 'class_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_subjects', 'subject_id', 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }



}
