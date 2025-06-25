<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultRecord extends Model
{
    use HasFactory;



    protected $fillable = [
        'result_id',
        'student_id',
        'marks_obtained',
        'total_marks',
        'grade',
        'remarks',
        'attendance_status',
    ];

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }


    

}
