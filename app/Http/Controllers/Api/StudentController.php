<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Student;
use App\Models\FeeSlip;
use App\Models\Guardian;
use App\Models\Enrollment;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\Account;
use App\Models\Extra;
use App\Models\Fee;
use App\Models\FeeStructure;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Result;
use App\Models\ResultRecord;


class StudentController extends Controller
{



    /* public function getResultsByTerm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'term' => 'required|string|max:50',
            'year' => 'required|integer|min:2000',
            'exam' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::with('enrollment')->find($request->student_id);

        if (!$student || !$student->enrollment) {
            return response()->json(['error' => 'Student or enrollment not found.'], 404);
        }

        $section_id = $student->enrollment->section_id;

        // Get all subjects for the section
        $section = Section::with('subjects')->find($section_id);
        if (!$section) {
            return response()->json(['error' => 'Section not found.'], 404);
        }
        $subjects = $section->subjects;

        // Fetch results for each subject
        $results = [];
        foreach ($subjects as $subject) {
            //$result = Result::where('student_id', $student->id)
            $result = Result::where('subject_id', $subject->id)
                ->where('term', $request->term)
                ->where('year', $request->year)
                ->where('exam', $request->exam)
                ->first();

            $results[] = [
                'subject' => $subject,
                'result' => $result,
            ];
        }

        return response()->json([
            'student_id' => $student->id,
            'term' => $request->term,
            'year' => $request->year,
            'exam' => $request->exam,
            'results' => $results,
        ]);
    } */


    public function getResultsByTerm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'term' => 'required|string|max:50',
            'year' => 'required|integer|min:2000',
            'exam' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::with('enrollment')->find($request->student_id);

        if (!$student || !$student->enrollment) {
            return response()->json(['error' => 'Student or enrollment not found.'], 404);
        }

        $section_id = $student->enrollment->section_id;

        // Get all subjects for the section
        $section = Section::with('subjects')->find($section_id);
        if (!$section) {
            return response()->json(['error' => 'Section not found.'], 404);
        }
        $subjects = $section->subjects;

        // Fetch results and result records for each subject
        $results = [];
        foreach ($subjects as $subject) {
            $result = Result::where('subject_id', $subject->id)
                ->where('term', $request->term)
                ->where('year', $request->year)
                ->where('exam', $request->exam)
                ->first();

            $resultRecord = null;
            if ($result) {
                $resultRecord = ResultRecord::where('result_id', $result->id)
                    ->where('student_id', $student->id)
                    ->first();
            }

            $results[] = [
                'subject' => $subject,
                'result' => $result,
                'result_record' => $resultRecord,
            ];
        }

        return response()->json([
            'student_id' => $student->id,
            'term' => $request->term,
            'year' => $request->year,
            'exam' => $request->exam,
            'results' => $results,
        ]);
    }


    public function getAttendanceByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::with('enrollment')->find($request->student_id);

        if (!$student || !$student->enrollment) {
            return response()->json(['error' => 'Student or enrollment not found.'], 404);
        }

        $class_id = $student->enrollment->class_id;
        $section_id = $student->enrollment->section_id;

        $attendance = Attendance::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('date', $request->date)
            ->first();

        if (!$attendance) {
            return response()->json(['error' => 'Attendance record not found.'], 404);
        }

        $attendanceRecord = AttendanceRecord::where('attendance_id', $attendance->id)
            ->where('student_id', $student->id)
            ->first();

        return response()->json([
            'attendance' => $attendance,
            'attendance_record' => $attendanceRecord,
        ]);
    }


    public function getMonthlyAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::with('enrollment')->find($request->student_id);

        if (!$student || !$student->enrollment) {
            return response()->json(['error' => 'Student or enrollment not found.'], 404);
        }

        $class_id = $student->enrollment->class_id;
        $section_id = $student->enrollment->section_id;
        $year = $request->year;
        $month = $request->month;

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();

        $attendances = Attendance::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get();

        $attendanceData = [];

        foreach ($attendances as $attendance) {
            $attendanceRecord = AttendanceRecord::where('attendance_id', $attendance->id)
                ->where('student_id', $student->id)
                ->first();

            $attendanceData[] = [
                'date' => $attendance->date,
                'attendance' => $attendance,
                'attendance_record' => $attendanceRecord,
            ];
        }

        return response()->json([
            'student_id' => $student->id,
            'month' => $month,
            'year' => $year,
            'attendance' => $attendanceData,
        ]);
    }


    public function updateStudentAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'fine' => 'nullable|numeric|min:0',
            'dues' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::with('account')->find($request->student_id);

        if (!$student || !$student->account) {
            return response()->json(['error' => 'Student account not found.'], 404);
        }

        $student->account->update([
            'fine' => $request->fine,
            'dues' => $request->dues,
            'discount_type' => $request->discount_type,
            'discount_amount' => $request->discount_amount,
        ]);

        return response()->json([
            'message' => 'Student account updated successfully.',
            'account' => $student->account,
        ]);
    }

    


    public function getAccountWithFeeStructure($student_id)
    {
        if (!$student_id) return response()->json(['error' => 'Student ID is required.'], 400);
        $student = Student::with(['account', 'myClass.feeStructure'])->find($student_id);


        if (!$student || !$student->account) {
            return response()->json(['error' => 'Account or student not found.'], 404);
        }


        $feeStructure = $student->myClass->feeStructure;

        return response()->json([
            'account' => $student->account,
            'fee_structure' => $feeStructure,
        ]);
    }



    public function getFeeHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'month' => 'required|string',
            'year' => 'required|integer|min:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::with('enrollment')->find($request->student_id);

        if (!$student || !$student->enrollment) return response()->json(['error' => 'Student or enrollment not found.'], 404);
        

        $class_id = $student->enrollment->class_id;
        $section_id = $student->enrollment->section_id;

        $fees = Fee::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->where('type', 'monthly')
            ->get();

        $result = [];

        foreach ($fees as $fee) {
            $slips = FeeSlip::where('fee_id', $fee->id)
                ->where('student_id', $student->id)
                ->get();
            $result[] = [
                'fee' => $fee,
                'fee_slips' => $slips,
            ];
        }

        return response()->json([
            'fees' => $result,
        ]);
    }


    public function getMonthlyStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'year' => 'required|integer|min:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = Student::with('enrollment')->find($request->student_id);

        if (!$student || !$student->enrollment) {
            return response()->json(['error' => 'Student or enrollment not found.'], 404);
        }

        $class_id = $student->enrollment->class_id;
        $section_id = $student->enrollment->section_id;
        $year = $request->year;

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $monthlyStatus = [];

        foreach ($months as $month) {
            $fee = Fee::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('type', 'monthly')
                ->first();

            $feeSlip = null;
            $status = 'not_generated';

            if ($fee) {
                $feeSlip = FeeSlip::where('fee_id', $fee->id)
                    ->where('student_id', $student->id)
                    ->first();

                if ($feeSlip) {
                    $status = $feeSlip->status; // e.g., 'paid', 'unpaid', etc.
                } else {
                    $status = 'not_issued';
                }
            }

            $monthlyStatus[] = [
                'month' => $month,
                'fee' => $fee,
                'fee_slip' => $feeSlip,
                'status' => $status,
            ];
        }

        return response()->json([
            'monthly_status' => $monthlyStatus,
        ]);
    }


    public function getStudentById($id)
    {
        $student = Student::with(['guardian', 'enrollment','extras'])->find($id);

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
        })->with(['guardian', 'enrollment' , 'account'])->get();

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
