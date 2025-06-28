<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

use App\Models\MyClass;
use App\Models\Section;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\EmployeeQualification;

class EmployeeController extends Controller
{

    protected function validateAddEmployee(Request $request)
    {
        return Validator::make($request->all(), [
            'full_name'      => 'required|string|max:255',
            'father_name'    => 'required|string|max:255',
            'gender'         => 'required|string|max:10',
            'dob'            => 'required|date',
            'martial_status' => 'required|string|max:50',
            'cnic'           => 'required|string|max:20|unique:employees,cnic',
            'address'        => 'required|string|max:500',
            'blood_group'    => 'nullable|string|max:10',
            'health_profile' => 'nullable|string|max:1000',
            'photo'          => 'nullable|string|max:255',
            'email'          => 'required|email|unique:employees,email',
            'phone'          => 'required|string|max:20',
        ], [
            'full_name.required'      => 'Full name is required.',
            'father_name.required'    => 'Father name is required.',
            'gender.required'         => 'Gender is required.',
            'dob.required'            => 'Date of birth is required.',
            'martial_status.required' => 'Martial status is required.',
            'cnic.required'           => 'CNIC is required.',
            'cnic.unique'             => 'This CNIC is already taken.',
            'address.required'        => 'Address is required.',
            'email.required'          => 'Email is required.',
            'email.email'             => 'Email must be a valid email address.',
            'email.unique'            => 'This email is already taken.',
            'phone.required'          => 'Phone is required.',
        ]);
    }

    protected function validateAddQualification(Request $request)
    {
        return Validator::make($request->all(), [
            'employee_id'     => 'required|exists:employees,id',
            'role'            => 'required|string|max:100',
            'qualification'   => 'required|string|max:255',
            'institute'       => 'required|string|max:255',
            'year_of_passing' => 'required|integer|min:1900|max:' . date('Y'),
            'joining_date'    => 'required|date',
            'specialization'  => 'nullable|string|max:255',
            'department'      => 'nullable|string|max:255',
            'experience'      => 'nullable|string|max:100',
            'grade'           => 'nullable|string|max:50',
            'remarks'         => 'nullable|string|max:500',
            'document_path'   => 'nullable|string|max:255',
        ], [
            'employee_id.required'     => 'Employee ID is required.',
            'employee_id.exists'       => 'Employee not found.',
            'role.required'            => 'Role is required.',
            'qualification.required'   => 'Qualification is required.',
            'institute.required'       => 'Institute is required.',
            'year_of_passing.required' => 'Year of passing is required.',
            'year_of_passing.integer'  => 'Year of passing must be an integer.',
            'year_of_passing.min'      => 'Year of passing is invalid.',
            'year_of_passing.max'      => 'Year of passing cannot be in the future.',
            'joining_date.required'    => 'Joining date is required.',
        ]);
    }

    /*
    Example Postman input for 'year_of_passing':

    {
        "employee_id": 1,
        "role": "teacher",
        "qualification": "MSc Mathematics",
        "institute": "University of Lahore",
        "year_of_passing": 2020,
        "joining_date": "2021-08-15",
        "specialization": "Algebra",
        "department": "Mathematics",
        "experience": "2 years",
        "grade": "A",
        "remarks": "Excellent academic record",
        "document_path": "/uploads/qualifications/msc-math.pdf"
    }

    Note: 'year_of_passing' must be an integer between 1900 and the current year (e.g., 2024).
    */

    public function assignInchargeToSection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'class_id'    => 'required|exists:my_classes,id',
            'section_id'  => 'required|exists:sections,id',
        ], [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.exists'   => 'Employee not found.',
            'class_id.required'    => 'Class ID is required.',
            'class_id.exists'      => 'Class not found.',
            'section_id.required'  => 'Section ID is required.',
            'section_id.exists'    => 'Section not found.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Assuming Employee has a hasOne relation 'incharge' to a model (e.g., Incharge)
        $employee = Employee::findOrFail($validated['employee_id']);

        // Remove previous incharge assignment for this section if exists
        $employee->incharge()->updateOrCreate(
            ['section_id' => $validated['section_id']],
            [
                'class_id'    => $validated['class_id'],
                'section_id'  => $validated['section_id'],
                'employee_id' => $validated['employee_id'],
            ]
        );

        return response()->json([
            'message' => 'Incharge assigned to section successfully'
        ]);
    }


    public function getIncharge()
    {
        $incharges = Employee::where('status', 'active')
            ->whereHas('incharge')
            ->with(['incharge.class', 'incharge.section'])
            ->orderBy('full_name')
            ->get();

        return response()->json(['incharges' => $incharges]);
    }

    public function getInchargeByEmployeeId($id)
    {
        $employee = Employee::where('status', 'active')
            ->where('id', $id)
            ->whereHas('incharge')
            ->with(['incharge.class', 'incharge.section'])
            ->first();

        if (!$employee) {
            return response()->json(['error' => 'employee not found'], 404);
        }

        return response()->json(['employee' => $employee]);
    }

    public function getSubjectsByEmployeeId($id)
    {
        $employee = Employee::where('status', 'active')
            ->where('id', $id)
            ->with(['sectionSubjects.subject', 'sectionSubjects.section', 'sectionSubjects.myClass'])
            ->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $subjectsWithSections = $employee->sectionSubjects->map(function ($sectionSubject) {
            return [
                'subject' => $sectionSubject->subject,
                'section' => $sectionSubject->section,
                'class'   => $sectionSubject->myClass ,
            ];
        })->filter(function ($item) {
            return $item['subject'] && $item['section'] && $item['class'];
        })->values();

        return response()->json(['subjects' => $subjectsWithSections]);
    }

    protected function validateGetQualifications(Request $request)
    {
        return Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ], [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.exists'   => 'Employee not found.',
        ]);
    }

    public function addEmployee(Request $request)
    {
        $validator = $this->validateAddEmployee($request);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $validated = $validator->validated();









        

        
        $employee = new Employee($validated);
        $employee->save();




        return response()->json([
            'message'  => 'Employee added successfully',
            'employee' => $employee
        ], 201);
    }

    public function getEmployees()
    {
        $employees = Employee::where('status', 'active')
            ->with('qualification')
            ->get();
        return response()->json([
            'employees' => $employees
        ]);
    }

    public function getPendingEmployees()
    {
        $employees = Employee::where('status', 'pending')->get();
        return response()->json([
            'employees' => $employees
        ]);
    }



    public function getTeachers()
    {
        $teachers = Employee::where('status', 'active')
            ->whereHas('qualification', function ($q) {
                $q->where('role', 'teacher');
            })
            ->with(['qualification' => function ($q) {
                $q->where('role', 'teacher');
            }])
            ->orderBy('full_name')
            ->get();

        return response()->json(['teachers' => $teachers]);
    }


    public function addQualification(Request $request)
    {
        $validator = $this->validateAddQualification($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $employee = Employee::findOrFail($request->employee_id);

        $data = $validator->validated();

        // Set staff_id in qualification if not set
        $data['staff_id'] = $this->getNextStaffId();

        $employee_qualification = $employee->qualification()->create($data);

        // Set employee status to active if not already
        $employee->status = 'active';
        $employee->save();

        return response()->json([
            'message' => 'Qualification added successfully',
            'qualification' => $employee_qualification
        ], 201);
    }

    /* public function addQualification(Request $request)
    {
        $validator = $this->validateAddQualification($request);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $employee = Employee::findOrFail($validated['employee_id']);

        // Make sure the relationship is named 'qualifications' and uses 'employee_qualifications' table
        $qualification = $employee->qualification()->create($validated);

        // Reload employee with qualifications
        //$employee->load('qualifications');

        return response()->json([
            'message' => 'Qualification added successfully',
            'employee' => $employee
        ], 201);
    } */

    public function getQualifications(Request $request)
    {
        $validator = $this->validateGetQualifications($request);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $employee = Employee::with('qualifications')->findOrFail($validated['employee_id']);

        return response()->json([
            'qualifications' => $employee->qualifications
        ]);
    }



    protected function getNextStaffId()
    {
        $lastOne = EmployeeQualification::orderBy('staff_id', 'desc')->first();
        if (!$lastOne || !$lastOne->staff_id) {
            return 10000; // Default starting staff ID
        }
        return (int) $lastOne->staff_id + 1;
    }



}
