<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subjects';
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'type'
    ];
    protected $attributes = [
        'status' => 'active', // Default status
    ];

    protected $casts = [
        'name' => 'string',
        'code' => 'string',
        'description' => 'string',
        'status' => 'string',
        'type' => 'string',
    ];


    // Relationships

    public function sectionSubjects()
    {
        return $this->hasMany(SectionSubject::class, 'subject_id');
    }
    public function classes()
    {
        return $this->belongsToMany(MyClass::class, 'section_subjects', 'subject_id', 'class_id');
    }
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_subjects', 'subject_id', 'section_id');
    }

    




    


}
