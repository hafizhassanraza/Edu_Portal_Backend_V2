<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyClass extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'status',
        'disc',
    ];

    //Relationships
    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments', 'class_id', 'student_id');
    }





















    protected $attributes = [
        'status' => 'available', // Default status
    ];





    public function sections()
    {
        // This method defines a many-to-many relationship with the Section model
        return $this->belongsToMany(
            Section::class,    // Related model
            'class_sections',   // Pivot table name
            'class_id',        // Foreign key on pivot table for this model
            'section_id'       // Foreign key on pivot table for related model
        );
    }







    
    public function feeStructure()
    {
        return $this->hasOne(FeeStructure::class, 'class_id');
    }
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }
    public function accounts()
    {
        return $this->hasMany(
            Account::class,
            'student_id', // Foreign key on accounts table...
            'id'          // Local key on this model...
        );
    }

    public function sectionSubjects()
    {
        return $this->hasMany(SectionSubject::class, 'class_id');
    }
    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'class_subjects', // Pivot table name
            'class_id',       // Foreign key on pivot table for this model
            'subject_id'      // Foreign key on pivot table for related model
        );
    }

}
