<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeSlip extends Model
{
    use HasFactory;

    protected $table = 'fee_slips';
    protected $fillable = [
        'fee_id',
        'type',
        'student_id',
        'challan_number',
        'issue_date',
        'due_date',
        'receiving_date',
        'admission_fee',
        'tuition_fee',
        'hostel_fee',
        'exam_fee',
        'transport_fee',
        'library_fee',
        'lab_fee',
        'medical_fee',
        'sports_fee',
        'utility_charges',
        'other',
        'total',
        'fine',
        'dues',
        'discount',
        'payable',
        'paid_amount',
        'remaining_amount',
        'status',
        'payment_method'
    ];
    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'total' => 'integer',
        'fine' => 'integer',
        'dues' => 'integer',
        'discount' => 'integer',
        'payable' => 'integer',
        'paid_amount' => 'integer',
        'remaining_amount' => 'integer'
    ];
    protected $attributes = [
        'challan_number' => 10000,

        'admission_fee' => 0,
        'tuition_fee' => 0,
        'hostel_fee' => 0,
        'exam_fee' => 0,
        'transport_fee' => 0,
        'library_fee' => 0,
        'lab_fee' => 0,
        'medical_fee' => 0,
        'sports_fee' => 0,
        'utility_charges' => 0,
        'other' => 0,
        'total' => 0,
        'fine' => 0,
        'dues' => 0,
        'discount' => 0,
        'payable' => 0,
        'paid_amount' => 0,
        'remaining_amount' => 0,
        'receiving_date' => 'null',
        'status' => 'unpaid',
        'payment_method' => 'cash'
    ];
    // Relationships
    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
   

}
