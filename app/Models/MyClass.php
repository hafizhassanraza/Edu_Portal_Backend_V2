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
    protected $attributes = [
        'status' => 'available', // Default status
    ];
    //Relationships
    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments', 'class_id', 'student_id');
    }
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

}
