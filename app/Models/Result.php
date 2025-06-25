<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'section_id',
        'subject_id',
        'employee_id',
        'exam',
        'term',
        'year',
        'total_marks',
        'passing_marks',
    ];

    public function class()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function resultRecords()
    {
        return $this->hasMany(ResultRecord::class, 'result_id');
    }


}
