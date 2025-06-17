<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\FeeStructure;
use App\Models\Fee;
use App\Models\FeeSlip;
use App\Models\Account;



class FeeController extends Controller
{
    


    // --------------------------------------------------------------------------|
    // ----------------- Fee & Fee-Slips Structure Management -------------------|
    // --------------------------------------------------------------------------|






    public function getFeesByYearAndMonth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000',
            'month' => 'required|string',
        ], [
            'year.required' => 'The year is required.',
            'year.integer' => 'The year must be an integer.',
            'year.min' => 'The year must be at least 2000.',
            'month.required' => 'The month is required.',
            'month.string' => 'The month must be a string.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fees = Fee::with(['feeSlips', 'myClass'])
            ->where('year', $request->input('year'))
            ->where('month', $request->input('month'))
            ->get();

        return response()->json([
            'fees' => $fees
        ]);
    }


    public function getPendingFeeSlips(Request $request)
    {
        $query = FeeSlip::with('student')->where('status', '!=', 'paid');

        if ($request->has('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->input('class_id'));
            });
        }

        if ($request->has('section_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('section_id', $request->input('section_id'));
            });
        }

        $pendingFeeSlips = $query->get();

        return response()->json([
            'pending_fee_slips' => $pendingFeeSlips
        ]);
    }

    public function getFeeSlipByChallan($challanNumber)
    {
        $feeSlip = FeeSlip::with('student')->where('challan_number', $challanNumber)->first();
        if (!$feeSlip) {
            return response()->json(['error' => 'Fee slip not found.'], 404);
        }
        return response()->json(['fee_slip' => $feeSlip]);
    }

    public function payFeeSlip(Request $request)
    {
        
        $validator = $this->validatePayFeeSlip($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        

        $feeSlip = FeeSlip::find($request->input('id'));
        if (!$feeSlip) return response()->json(['error' => 'Fee slip not found.'], 404);
        

        $amount = $request->input('amount');
        $alreadyPaid = $feeSlip->paid_amount ?? 0;
        $totalPaid = $alreadyPaid + $amount;

        if ($totalPaid > $feeSlip->payable) {
            return response()->json(['error' => 'Paid amount exceeds payable amount.'], 400);
        }

        $feeSlip->paid_amount = $totalPaid;
        if ($totalPaid == $feeSlip->payable) {
            $feeSlip->status = 'paid';
        } elseif ($totalPaid > 0) {
            $feeSlip->status = 'partially_paid';
        } else {
            $feeSlip->status = 'unpaid';
        }
        $feeSlip->save();

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'fee_slip' => $feeSlip
        ]);
    }




    public function getChallans(Request $request)
    {
        $validator = $this->validateFee($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        $fees = Fee::with('feeSlips')->where([
            'year' => $request->year,
            'month' => $request->month,
            'class_id' => $request->class_id,
            'type' => $request->type,
            ])->get();

        $feeSlips = $fees->flatMap(function ($fee) { return $fee->feeSlips; })->values();

        return response()->json([
            'fee_slips' => $feeSlips
        ]);
    }

    protected function validateFee(Request $request)
    {
        return Validator::make($request->all(), [
            'year' => 'required|integer|min:2000',
            'month' => 'required|string',
            'class_id' => 'required|exists:my_classes,id',
            'type' => 'required|in:monthly,admission',
        ], [
            'year.required' => 'The year is required.',
            'year.integer' => 'The year must be an integer.',
            'year.min' => 'The year must be at least 2000.',
            'month.required' => 'The month is required.',
            'month.string' => 'The month must be a string.',
            'class_id.required' => 'The class ID is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'type.required' => 'The fee type is required.',
            'type.in' => 'The fee type must be either monthly or admission.',
        ]);
    }
    
    
    public function addFeeSlips(Request $request)
    {
        $validator = $this->validateFeeSlip($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $createdFeeSlips = [];
        $data = $validator->validated();
        $fee = $this->feeFinderCreator($data); // Get From Fee Table
        $challan_number = $this->lastChallanNumberFinder(); // Get Last Fee Slip Chalan Number From FeeSlips Table
        $isMonthly = ($data['type'] ?? null) === 'monthly';


        foreach ($data['fee_slips'] as $slip) {

            $challan_number++; 

            $slip['challan_number'] = $challan_number; 

            $slip['type'] = $data['type']; // Ensure 'type' is set for each slip

            $slip = $this->calculateTotalAndPayable($slip, $isMonthly); 

            $createdFeeSlips[] = $fee->feeSlips()->create($slip);

        }

        return response()->json([
            'message' => 'Fee slips added successfully.',
            'fee_slips' => $createdFeeSlips,
        ], 201);

    }





    // --------------------------------------------------------------------------|
    // ------------------------ Fee Structure Management ------------------------|
    // --------------------------------------------------------------------------|

    public function getFeeStructures()
    {
        $feeStructures = FeeStructure::with('myClass')->get();
        return response()->json([
            'feeStructures' => $feeStructures
        ]);
    }

    public function getFeeStructureByClassID($classId)
    {
        $feeStructure = FeeStructure::where('class_id', $classId)->with('myClass')->first();
        if (!$feeStructure) {
            return response()->json(['error' => 'Fee structure not found for this class.'], 404);
        }
        return response()->json(['feeStructure' => $feeStructure]);
    }

    public function getFeeStructureByID($id)
    {
        $feeStructure = FeeStructure::find($id);
        if (!$feeStructure) {
            return response()->json(['error' => 'Fee structure not found.'], 404);
        }
        return response()->json(['feeStructure' => $feeStructure]);
    }

    public function addFeeStructure(Request $request)
    {
        $validator = $this->validateFeeStructure($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        

        $myClass = MyClass::find($request->class_id);
        $feeStructure = $myClass->feeStructure()->create($validator->validated());

        return response()->json([
            'message' => 'Fee structure added successfully.',
            'feeStructure' => $feeStructure,
            'class' => $myClass
        ], 201);
    }

    
    // --------------------------------------------------------------------------|
    //*********************** private validation/Finder methods *****************|
    //---------------------------------------------------------------------------|


    protected function validateFeeStructure(Request $request)
    {
        return Validator::make($request->all(), [
            'class_id' => 'required|exists:my_classes,id',
            'admission_fee' => 'required|numeric|min:0',
            'tuition_fee' => 'required|numeric|min:0',
            'hostel_fee' => 'nullable|numeric|min:0',
            'exam_fee' => 'nullable|numeric|min:0',
            'transport_fee' => 'nullable|numeric|min:0',
            'library_fee' => 'nullable|numeric|min:0',
            'lab_fee' => 'nullable|numeric|min:0',
            'medical_fee' => 'nullable|numeric|min:0',
            'sports_fee' => 'nullable|numeric|min:0',
            'utility_charges' => 'nullable|numeric|min:0',
            'other' => 'nullable|numeric|min:0',
        ],
        [
            'class_id.required' => 'The class ID is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'admission_fee.required' => 'The admission fee is required.',
            'admission_fee.numeric' => 'The admission fee must be a number.',
            'admission_fee.min' => 'The admission fee must be at least 0.',
            'tuition_fee.required' => 'The tuition fee is required.',
            'tuition_fee.numeric' => 'The tuition fee must be a number.',
            'tuition_fee.min' => 'The tuition fee must be at least 0.',
            'hostel_fee.numeric' => 'The hostel fee must be a number.',
            'hostel_fee.min' => 'The hostel fee must be at least 0.',
            'exam_fee.numeric' => 'The exam fee must be a number.',
            'exam_fee.min' => 'The exam fee must be at least 0.',
            'transport_fee.numeric' => 'The transport fee must be a number.',
            'transport_fee.min' => 'The transport fee must be at least 0.',
            'library_fee.numeric' => 'The library fee must be a number.',
            'library_fee.min' => 'The library fee must be at least 0.',
            'lab_fee.numeric' => 'The lab fee must be a number.',
            'lab_fee.min' => 'The lab fee must be at least 0.',
            'medical_fee.numeric' => 'The medical fee must be a number.',
            'medical_fee.min' => 'The medical fee must be at least 0.',
            'sports_fee.numeric' => 'The sports fee must be a number.',
            'sports_fee.min' => 'The sports fee must be at least 0.',
            'utility_charges.numeric' => 'The utility charges must be a number.',
            'utility_charges.min' => 'The utility charges must be at least 0.',
            'other.numeric' => 'The other fee must be a number.',
            'other.min' => 'The other fee must be at least 0.',
        ]);
    }





    
    protected function validateFeeSlip(Request $request)
    {
        $type = $request->input('type');

        $rules = [
            'class_id' => 'required|exists:my_classes,id',
            'type' => 'required|in:monthly,admission',
            'month' => 'required|string',
            'year' => 'required|integer|min:2000',
            'due_date' => 'required|date',
            'issue_date' => 'required|date',
            'fee_slips' => 'required|array|min:1',
            'fee_slips.*.student_id' => 'required|exists:students,id',
            'fee_slips.*.issue_date' => 'required|date',
            'fee_slips.*.due_date' => 'required|date|after_or_equal:fee_slips.*.issue_date',
            'fee_slips.*.admission_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.tuition_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.hostel_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.exam_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.transport_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.library_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.lab_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.medical_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.sports_fee' => 'nullable|numeric|min:0',
            'fee_slips.*.utility_charges' => 'nullable|numeric|min:0',
            'fee_slips.*.other' => 'nullable|numeric|min:0',
            'fee_slips.*.discount' => 'nullable|numeric|min:0'
        ];

        if ($type === 'admission') {
            $rules['section_id'] = 'nullable|exists:sections,id';
        } else {
            $rules['section_id'] = 'required|exists:sections,id';
        }

        return Validator::make($request->all(), $rules, [
            'class_id.required' => 'The class ID is required.',
            'class_id.exists' => 'The selected class does not exist.',
            'section_id.required' => 'The section ID is required.',
            'section_id.exists' => 'The selected section does not exist.',
            'type.required' => 'The fee type is required.',
            'type.in' => 'The fee type must be either monthly or admission.',
            'month.required' => 'The month is required.',
            'year.required' => 'The year is required.',
            'year.integer' => 'The year must be an integer.',
            'due_date.required' => 'The due date is required.',
            'due_date.date' => 'The due date must be a valid date.',
            'issue_date.required' => 'The issue date is required.',
            'issue_date.date' => 'The issue date must be a valid date.',
            'fee_slips.required' => 'At least one fee slip is required.',
            'fee_slips.array' => 'Fee slips must be an array.',
            'fee_slips.*.student_id.required' => 'The student ID is required.',
            'fee_slips.*.student_id.exists' => 'The selected student does not exist.',
            'fee_slips.*.issue_date.required' => 'The issue date is required.',
            'fee_slips.*.issue_date.date' => 'The issue date must be a valid date.',
            'fee_slips.*.due_date.required' => 'The due date is required.',
            'fee_slips.*.due_date.date' => 'The due date must be a valid date.',
            'fee_slips.*.due_date.after_or_equal' => 'The due date must be after or equal to the issue date.',
            'fee_slips.*.admission_fee.numeric' => 'The admission fee must be a number.',
            'fee_slips.*.admission_fee.min' => 'The admission fee must be at least 0.',
            'fee_slips.*.tuition_fee.numeric' => 'The tuition fee must be a number.',
            'fee_slips.*.tuition_fee.min' => 'The tuition fee must be at least 0.',
            'fee_slips.*.hostel_fee.numeric' => 'The hostel fee must be a number.',
            'fee_slips.*.hostel_fee.min' => 'The hostel fee must be at least 0.',
            'fee_slips.*.exam_fee.numeric' => 'The exam fee must be a number.',
            'fee_slips.*.exam_fee.min' => 'The exam fee must be at least 0.',
            'fee_slips.*.transport_fee.numeric' => 'The transport fee must be a number.',
            'fee_slips.*.transport_fee.min' => 'The transport fee must be at least 0.',
            'fee_slips.*.library_fee.numeric' => 'The library fee must be a number.',
            'fee_slips.*.library_fee.min' => 'The library fee must be at least 0.',
            'fee_slips.*.lab_fee.numeric' => 'The lab fee must be a number.',
            'fee_slips.*.lab_fee.min' => 'The lab fee must be at least 0.',
            'fee_slips.*.medical_fee.numeric' => 'The medical fee must be a number.',
            'fee_slips.*.medical_fee.min' => 'The medical fee must be at least 0.',
            'fee_slips.*.sports_fee.numeric' => 'The sports fee must be a number.',
            'fee_slips.*.sports_fee.min' => 'The sports fee must be at least 0.',
            'fee_slips.*.utility_charges.numeric' => 'The utility charges must be a number.',
            'fee_slips.*.utility_charges.min' => 'The utility charges must be at least 0.',
            'fee_slips.*.other.numeric' => 'The other fee must be a number.',
            'fee_slips.*.other.min' => 'The other fee must be at least 0.',

            'fee_slips.*.discount.numeric' => 'The discount must be a number.',
            'fee_slips.*.discount.min' => 'The discount must be at least 0.',

        ]);
    }


    protected function validatePayFeeSlip(Request $request)
    {
        return Validator::make($request->all(), [
            'id' => 'required|exists:fee_slips,id',
            'amount' => 'required|numeric|min:0.01',
        ], [
            'id.required' => 'The fee slip ID is required.',
            'id.exists' => 'The selected fee slip does not exist.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.01.',
        ]);
    }



    // -----------------------------Finders---------------------------------------------|
   


    protected function calculateTotalAndPayable($slip, $isMonthly = false)
    {
        $discount = $slip['discount'] ?? 0;
        $fine = $slip['fine'] ?? 0;
        $dues = $slip['dues'] ?? 0;
            
        // If type is monthly, get dues, fine, discount from Account if available
        if ($isMonthly && isset($slip['student_id'])) {
            $account = Account::where('student_id', $slip['student_id'])->first();
            if ($account) {
                $d0ues = $account->dues ?? 0;
                $fine = $account->fine ?? 0;
                $discount = $account->discount ?? 0;
            }
        }

        // Calculate Total 
        $total = ($slip['admission_fee'])+($slip['tuition_fee'])+($slip['hostel_fee'])+($slip['exam_fee'])+($slip['transport_fee'])+($slip['library_fee'])
            +($slip['lab_fee'])+($slip['medical_fee'])+($slip['sports_fee'])+($slip['utility_charges'])+($slip['other']);


        // Payable = total - discount - fine - dues, but not less than 0
        $payable = $total - $discount - $fine - $dues;
        $payable = $payable < 0 ? 0 : $payable;

        $slip['total'] = $total;
        $slip['discount'] = $discount;
        $slip['fine'] = $fine;
        $slip['dues'] = $dues;
        $slip['payable'] = $payable;

        return $slip;
    }



    protected function feeFinderCreator(array $data)
    {
        return Fee::firstOrCreate(
            [
                'class_id' => $data['class_id'],
                'type' => $data['type'],
                'month' => $data['month'],
                'year' => $data['year'],
            ],
            [
                'section_id' => $data['section_id'] ?? null,
                'due_date' => $data['due_date'],
                'issue_date' => $data['issue_date'],
            ]
        );
    }
    
    protected function lastChallanNumberFinder()
    {
        $lastChallan = FeeSlip::orderBy('challan_number', 'desc')->first();
        if (!$lastChallan) return 10000; // Default starting challan number
        return $lastChallan->challan_number ;

    }


    


 

}
