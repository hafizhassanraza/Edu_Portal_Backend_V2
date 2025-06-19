<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $table = 'sections';











    protected $fillable = [
        'name',
        'status',
        'disc'
    ];



















    


    public function classes()
    {
        return $this->belongsTo(MyClass::class, 'class_id');
    }



    protected $casts = [
        'status' => 'string',
    ];
    protected $attributes = [
        'status' => 'available',
    ];
    //Relationships

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            Enrollment::class,
            'section_id',    // Foreign key on enrollments table...
            'id',            // Foreign key on students table...
            'id',            // Local key on sections table...
            'student_id'     // Local key on enrollments table...
        );
    }
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'section_id');
    }



}
