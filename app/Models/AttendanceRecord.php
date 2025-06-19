<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_id',
        'student_id',
        'status',
        'remarks',
    ];

    protected $casts = [
        'status' => 'string',
    ];
    protected $attributes = [
        'status' => 'present', // Default status
    ];
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    
}
