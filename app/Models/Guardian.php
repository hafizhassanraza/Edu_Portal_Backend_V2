<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'phone_number',
        'relation',
        'cnic',
        'email',
        'address',
        'occupation',
        'status',
        'photo'
    ];
    protected $attributes = [
        'status' => 'active', // Default status
    ];

    //Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }


}
