<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\FeeSlip;
use App\Models\Guardian;
use App\Models\Enrollment;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\Account;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{



    public function getStudentById($id)
    {
        $student = Student::with(['guardian', 'enrollment'])->find($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        return response()->json([
            'student' => $student,
        ]);
    }

    public function getStudentByRegNumber($reg_number)
    {
        $student = Student::whereHas('enrollment', function ($query) use ($reg_number) {
            $query->where('reg_number', $reg_number)
                  ->where('status', 'active');

        })->with(['guardian', 'enrollment'])->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found.'], 404);
        }

        return response()->json([
            'student' => $student,
        ]);
    }

    public function getStudentsByClassAndSection(Request $request)
    {
        $validator = $this->validateClassAndSection($request);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $students = Student::whereHas('enrollment', function ($query) use ($request) {
            $query->where('class_id', $request->class_id)
                  ->where('section_id', $request->section_id)
                  ->where('status', 'active');
        })->with(['guardian', 'enrollment'])->get();

        return response()->json([
            'students' => $students,
        ]);
    }

    public function getPendingStudents()
    {
        $students = Student::where('status', 'pending')->with([
            'guardian',
            'extras',
            'feeSlips' => function ($query) {
                $query->where('type', 'admission')->latest()->limit(1);
            }
        ])->get();

        return response()->json([
            'students' => $students,
        ]);
    }

    public function getPendingStudentById($id)
    {
        $student = Student::where('id', $id)->where('status', 'pending')->with([
            'guardian',
            'extras',
            'feeSlips' => function ($query) {
                $query->where('type', 'admission')->latest()->limit(1);
            }
        ])->first();

        if (!$student) { return response()->json(['error' => 'Pending student not found.'], 404);}

        return response()->json([
            'student' => $student,
        ]);
    }


    public function getGuardianByStudentID($student_id)
    {
        $student = Student::find($student_id);

        if (!$student) return response()->json(['error' => 'Student not found.'], 404);
        
        $guardian = $student->guardian;

        return response()->json([
            'student' => $student,
        ]);
    }


    public function addStudent(Request $request)
    {
        $validator = $this->validateStudent($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        $student = Student::create($validator->validated());

        return response()->json([
            'message' => 'Student Profile Created Successfully!',
            'student' => $student,
        ], 201);
    }


    public function addGuardian(Request $request)
    {
        $validator = $this->validateGuardian($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        $student = Student::find($request->student_id);
        $guardian = $student->guardian()->create($validator->validated());

        return response()->json([
            'message' => 'Guardian added successfully!',
            'guardian' => $guardian,
            'student' => $student,
        ], 201);

    }

    public function addExtra(Request $request)
    {
        $validator = $this->validateExtra($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $student = Student::find($request->student_id);

        $extra = $student->extras()->updateOrCreate($validator->validated());

        return response()->json([
            'message' => 'Extra information added successfully!',
            'extra' => $extra,
            'student' => $student,
        ], 201);
    }

    public function addEnrollment(Request $request)
    {
        $validator = $this->validateEnrollment($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $student = Student::find($request->student_id);
        $feeSlip = FeeSlip::find($request->fee_slip_id);

        // Check if fee slip is paid
        if ($feeSlip->status !== 'paid') return response()->json(['error' => 'Fee slip must be paid before enrollment.'], 422);

        // Get next registration number
        $regNumber = $this->getNextRegNumber();

        // Set student status to active
        $student->status = 'active';

        // Prepare enrollment data
        $enrollmentData = $validator->validated();
        $enrollmentData['reg_number'] = $regNumber;

        // Save and create
        $student->account()->create([]);
        $enrollment = $student->enrollment()->create($enrollmentData);
        $student->save();

        return response()->json([
            'message' => 'Student enrolled successfully!',
            'enrollment' => $enrollment,
            'student' => $student,
        ], 201);
    }




    
    //------------------------------------------------------------------------------
    //*********************** private validation methods **************************|
    //------------------------------------------------------------------------------


    
    protected function validateExtra(Request $request)
    {
        return Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:my_classes,id',
            'hostel_assign' => 'nullable|string|max:255',
            'transport_assign' => 'nullable|string|max:255',
            'previous_school' => 'nullable|string|max:255',
        ], [
            'student_id.required' => 'The student ID is required.',
            'student_id.exists' => 'The student does not exist.',
            'class_id.required' => 'The class ID is required.',
            'class_id.exists' => 'The class does not exist.'
        ]);
    }

    
    protected function validateEnrollment(Request $request)
    {
        return Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:my_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'fee_slip_id' => 'required|exists:fee_slips,id',
            //'reg_number' => 'required|string|max:100|unique:enrollments,reg_number',
            'enrollment_date' => 'required|date',
            'academic_year' => 'nullable|string|max:50',
            'previous_school' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ], [
            'student_id.required' => 'The student ID is required.',
            'student_id.exists' => 'The student does not exist.',
            'class_id.required' => 'The class ID is required.',
            'class_id.exists' => 'The class does not exist.',
            'section_id.exists' => 'The section does not exist.',
            'fee_slip_id.required' => 'The fee slip ID is required.',
            'fee_slip_id.exists' => 'The fee slip does not exist.',
            //'reg_number.required' => 'The registration number is required.',
            //'reg_number.unique' => 'The registration number already exists.',
            'enrollment_date.required' => 'The enrollment date is required.',
            'enrollment_date.date' => 'The enrollment date must be a valid date.',
        ]);
    }


    protected function validateGuardian(Request $request)
    {
        return Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'relation' => 'required|string|max:100',
            'cnic' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'occupation' => 'nullable|string|max:100',
            'photo' => 'nullable|string|max:255',
        ], [
            'student_id.required' => 'The student ID is required.',
            'student_id.exists' => 'The student does not exist.',
            'name.required' => 'The guardian name is required.',
            'relation.required' => 'The relation is required.',
            'cnic.required' => 'The CNIC is required.',
        ]);
    }
    
    protected function validateStudent(Request $request)
    {
        return Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'gender' => 'required|string|max:50',
            'dob' => 'nullable|string|max:50',
            'b_form' => 'required|string|max:50|unique:students,b_form',
            'address' => 'nullable|string|max:500',
            'blood_group' => 'nullable|string|max:50',
            'health_profile' => 'nullable|string|max:255',
            'photo' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:students,email',
        ], [
            'full_name.required' => 'The full name is required.',
            'father_name.required' => 'The father name is required.',
            'gender.required' => 'The gender is required.',
            'b_form.required' => 'The B-Form is required.',
            'b_form.unique' => 'The B-Form already exists.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email already exists.',
        ]);
    }


    protected function validateClassAndSection(Request $request)
    {
        return Validator::make($request->all(), [
            'class_id' => 'required|exists:my_classes,id',
            'section_id' => 'required|exists:sections,id',
        ], [
            'class_id.required' => 'The class ID is required.',
            'class_id.exists' => 'The class does not exist.',
            'section_id.required' => 'The section ID is required.',
            'section_id.exists' => 'The section does not exist.',
        ]);
    }


    protected function getNextRegNumber()
    {
        $lastEnrollment = Enrollment::orderBy('reg_number', 'desc')->first();
        if (!$lastEnrollment) return 10000; // Default starting challan number
        $lastRegNumber = (int) $lastEnrollment->reg_number;
        return  $lastRegNumber + 1 ;
    }
}
