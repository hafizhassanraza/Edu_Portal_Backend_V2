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
    


}
