<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
     protected $fillable = [
        'class_id',
        'section_id',
        'date',
    ];
    protected $casts = [
        'date' => 'date',
    ];

    public function myClass()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'attendance_id');
    }
    public function students()
    {
        return $this->belongsToMany(Student::class, 'attendance_records', 'attendance_id', 'student_id');
    }
}
