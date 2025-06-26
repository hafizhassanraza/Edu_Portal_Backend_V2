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
use App\Models\Result;
use App\Models\ResultRecord;

class ExamController extends Controller
{


    public function index(Request $request)
    {
        $validator = $this->validateIndexRequest($request);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $results = Result::where($validated)->with(['resultRecords', 'class', 'section'])->get();

        $data = $results->map(function ($result) {
            $records = $result->resultRecords;
            $total_students = $records->count();
            $total_absent = $records->where('attendance_status', 'absent')->count();
            $present_records = $records->where('attendance_status', '!=', 'absent');
            $total_pass = $present_records->where('marks_obtained', '>=', $result->passing_marks)->count();
            $total_failed = $present_records->where('marks_obtained', '<', $result->passing_marks)->count();

            return [
                'result_id' => $result->id,
                'term' => $result->term,
                'year' => $result->year,
                'subject_id' => $result->subject_id,
                'class_id' => $result->class_id,
                'class_name' => optional($result->class)->name,
                'section_id' => $result->section_id,
                'section_name' => optional($result->section)->name,
                'employee_id' => $result->employee_id,
                'total_marks' => $result->total_marks,
                'passing_marks' => $result->passing_marks,
                'total_students' => $total_students,
                'total_pass' => $total_pass,
                'total_failed' => $total_failed,
                'total_absent' => $total_absent,
            ];
        });

        return response()->json(['results' => $data]);
    }


    public function getResultRecordsByResultId(Request $request)
    {
        $validator = $this->validateGetResultRecordsRequest($request);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = Result::with(['resultRecords.student.enrollment'])->find($request->input('result_id'));

        if (!$result) {
            return response()->json(['message' => 'Result not found.'], 404);
        }

        $records = $result->resultRecords->map(function ($record) {
            return [
            'result_id' => $record->result_id,
            'student_id' => $record->student_id,
            'marks_obtained' => $record->marks_obtained,
            'total_marks' => $record->total_marks,
            'grade' => $record->grade,
            'remarks' => $record->remarks,
            'attendance_status' => $record->attendance_status,
            'student_name' => optional($record->student)->full_name,
            'father_name' => optional($record->student)->father_name,
            'reg_number' => optional(optional($record->student)->enrollment)->reg_number,
            ];
        });

        return response()->json([
            'result_id' => $result->id,
            'exam' => $result->exam,
            'term' => $result->term,
            'year' => $result->year,
            'subject_id' => $result->subject_id,
            'class_id' => $result->class_id,
            'section_id' => $result->section_id,
            'result_records' => $records,
        ]);
    }
    



    // Validator for getResultRecordsByResultId
    protected function validateGetResultRecordsRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'result_id' => 'required|integer|exists:results,id',
        ], [
            'result_id.required' => 'Result ID is required.',
            'result_id.integer' => 'Result ID must be a number.',
            'result_id.exists' => 'Result not found.',
        ]);
    }





    // Separate function for index validation
    protected function validateIndexRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'term' => 'required|string|max:50',
            'year' => 'required|integer|min:2000|max:2100',
            'exam' => 'required|string|max:100',
            'subject_id' => 'required|integer|exists:subjects,id',
        ], [
            'term.required' => 'Term is required.',
            'term.max' => 'Term must not exceed 50 characters.',
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be at least 2000.',
            'year.max' => 'Year must not exceed 2100.',
            'exam.required' => 'Exam name is required.',
            'exam.max' => 'Exam name must not exceed 100 characters.',
            'subject_id.required' => 'Subject is required.',
            'subject_id.exists' => 'Selected subject does not exist.',
        ]);
    }


    public function store(Request $request)
    {
        $validator = $this->validateResultData($request);


        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        
        $validated = $validator->validated();

        // Check if result already exists for the given class, section, subject, exam, term, year
        $result = Result::where([
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'],
            'subject_id' => $validated['subject_id'],
            'employee_id' => $validated['employee_id'],
            'exam' => $validated['exam'],
            'term' => $validated['term'],
            'year' => $validated['year'],
        ])->first();

        if ($result) {
            // Update result fields
            $result->update([
                'total_marks' => $validated['total_marks'],
                'passing_marks' => $validated['passing_marks'],
            ]);
            // Delete existing result records
            $result->resultRecords()->delete();
        } else {
            // Create new result
            $result = Result::create($request->only([
                'class_id', 'section_id', 'subject_id', 'employee_id', 'exam', 'term', 'year', 'total_marks', 'passing_marks'
            ]));
        }

        // Create new result records
        foreach ($validated['result_records'] as $record) {
            $result->resultRecords()->create($record);
        }

        return response()->json([
            'message' => $result->wasRecentlyCreated ? 'Result and result records created.' : 'Result and result records updated.',
            'result' => $result->load('resultRecords'),
        ], $result->wasRecentlyCreated ? 201 : 200);
    }


    //Example JSON input for Postman:
    
    /* {
      "class_id": 1,
      "section_id": 2,
      "subject_id": 3,
      "employee_id": 4,
      "exam": "Mid Term",
      "term": "Spring",
      "year": 2024,
      "total_marks": 100,
      "passing_marks": 40,
      "result_records": [
        {
         "student_id": 10,
         "marks_obtained": 85,
         "total_marks": 100,
         "grade": "A",
         "remarks": "Excellent",
         "attendance_status": "present"
        },
        {
         "student_id": 11,
         "marks_obtained": 60,
         "total_marks": 100,
         "grade": "B",
         "remarks": "Good",
         "attendance_status": "present"
        }
      ]
    } */
    



    // Validator for Result
    // Unified validator for Result and Result Records (array)
    protected function validateResultData(Request $request)
    {
        return Validator::make($request->all(), [
            'class_id' => 'required|integer|exists:my_classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'exam' => 'required|string|max:100',
            'term' => 'required|string|max:50',
            'year' => 'required|integer|min:2000|max:2100',
            'total_marks' => 'required|numeric|min:0',
            'passing_marks' => 'required|numeric|min:0|max:' . $request->input('total_marks'),
            'result_records' => 'required|array|min:1',
            'result_records.*.result_id' => 'nullable|integer|exists:results,id',
            'result_records.*.student_id' => 'required|integer|exists:students,id',
            'result_records.*.marks_obtained' => 'required|numeric|min:0|max:' . $request->input('total_marks'),
            'result_records.*.total_marks' => 'required|numeric|min:0',
            'result_records.*.grade' => 'nullable|string|max:5',
            'result_records.*.remarks' => 'nullable|string|max:255',
            'result_records.*.attendance_status' => 'nullable|in:present,absent,leave',
        ], [
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'section_id.required' => 'Section is required.',
            'section_id.exists' => 'Selected section does not exist.',
            'subject_id.required' => 'Subject is required.',
            'subject_id.exists' => 'Selected subject does not exist.',
            'employee_id.required' => 'Teacher is required.',
            'employee_id.exists' => 'Selected teacher does not exist.',
            'exam.required' => 'Exam name is required.',
            'exam.max' => 'Exam name must not exceed 100 characters.',
            'term.required' => 'Term is required.',
            'term.max' => 'Term must not exceed 50 characters.',
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be at least 2000.',
            'year.max' => 'Year must not exceed 2100.',
            'total_marks.required' => 'Total marks are required.',
            'total_marks.numeric' => 'Total marks must be a number.',
            'total_marks.min' => 'Total marks must be at least 0.',
            'passing_marks.required' => 'Passing marks are required.',
            'passing_marks.numeric' => 'Passing marks must be a number.',
            'passing_marks.min' => 'Passing marks must be at least 0.',
            'passing_marks.max' => 'Passing marks cannot be greater than total marks.',
            'result_records.required' => 'At least one result record is required.',
            'result_records.array' => 'Result records must be an array.',
            'result_records.min' => 'At least one result record is required.',
            'result_records.*.result_id.integer' => 'Result ID must be a number.',
            'result_records.*.result_id.exists' => 'Result ID does not exist.',
            'result_records.*.student_id.required' => 'Student is required for each record.',
            'result_records.*.student_id.integer' => 'Student ID must be a number.',
            'result_records.*.student_id.exists' => 'Student does not exist.',
            'result_records.*.marks_obtained.required' => 'Marks obtained are required.',
            'result_records.*.marks_obtained.numeric' => 'Marks obtained must be a number.',
            'result_records.*.marks_obtained.min' => 'Marks obtained must be at least 0.',
            'result_records.*.marks_obtained.max' => 'Marks obtained cannot be greater than total marks.',
            'result_records.*.total_marks.required' => 'Total marks for each record are required.',
            'result_records.*.total_marks.numeric' => 'Total marks for each record must be a number.',
            'result_records.*.total_marks.min' => 'Total marks for each record must be at least 0.',
            'result_records.*.grade.max' => 'Grade must not exceed 5 characters.',
            'result_records.*.remarks.max' => 'Remarks must not exceed 255 characters.',
            'result_records.*.attendance_status.in' => 'Attendance status must be present, absent, or leave.',
        ]);
    }
    



}
