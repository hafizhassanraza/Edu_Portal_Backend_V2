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
use App\Models\Subject;
use App\Models\SectionSubject;


class AcademicController extends Controller
{
    //

    
    protected function validateAddSubject(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:subjects,name',
            'code' => 'required|string|max:50|unique:subjects,code',
            'type' => 'required|string|in:Practical,Theory',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Subject name is required.',
            'name.unique' => 'This subject name already exists.',
            'code.required' => 'Subject code is required.',
            'code.unique' => 'This subject code already exists.',
            'type.required' => 'Subject type is required.',
            'type.in' => 'Subject type must be either Practical or Theory.',
        ]);
    }

    protected function validateSubjectAssignment(Request $request)
    {
        return Validator::make($request->all(), [
            'class_id' => 'required|exists:my_classes,id',
            'section_id' => 'required|exists:sections,id',
            'subjects' => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.employee_id' => 'required|exists:employees,id',
        ], [
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'section_id.required' => 'Section is required.',
            'section_id.exists' => 'Selected section does not exist.',
            'subjects.required' => 'At least one subject assignment is required.',
            'subjects.array' => 'Subjects must be an array.',
            'subjects.*.subject_id.required' => 'Subject ID is required for each assignment.',
            'subjects.*.subject_id.exists' => 'Selected subject does not exist.',
            'subjects.*.employee_id.required' => 'Employee ID is required for each assignment.',
            'subjects.*.employee_id.exists' => 'Selected employee does not exist.',
        ]);
    }

    protected function validateGetAssignedSubjectsBySection(Request $request)
    {
        return Validator::make($request->all(), [
            'class_id' => 'required|exists:my_classes,id',
            'section_id' => 'required|exists:sections,id',
        ], [
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'section_id.required' => 'Section is required.',
            'section_id.exists' => 'Selected section does not exist.',
        ]);
    }

    // Update controller methods to use these validators:

    public function addSubject(Request $request)
    {
        $validator = $this->validateAddSubject($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validated = $validator->validated();

        $subject = Subject::create($validated);

        return response()->json([
            'message' => 'Subject added successfully.',
            'subject' => $subject,
        ], 201);
    }

    public function getSubjects()
    {
        $subjects = Subject::where('status', 'active')->get();

        return response()->json([
            'subjects' => $subjects,
        ]);
    }

    public function subjectAssignment(Request $request)
    {
        $validator = $this->validateSubjectAssignment($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validated = $validator->validated();

        $subjectIds = collect($validated['subjects'])->pluck('subject_id')->all();

        $existingAssignments = SectionSubject::where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->whereIn('subject_id', $subjectIds)
            ->pluck('subject_id')
            ->all();

        $newAssignments = [];
        foreach ($validated['subjects'] as $item) {
            if (!in_array($item['subject_id'], $existingAssignments)) {
                $newAssignments[] = [
                    'class_id' => $validated['class_id'],
                    'section_id' => $validated['section_id'],
                    'subject_id' => $item['subject_id'],
                    'employee_id' => $item['employee_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        if (!empty($newAssignments)) {
            SectionSubject::insert($newAssignments);
        }

        return response()->json([
            'message' => 'Subjects assigned to Section successfully.',
            'assignments' => $newAssignments,
        ], 201);
    }

    public function getAssignedSubjectsBySection(Request $request)
    {
        $validator = $this->validateGetAssignedSubjectsBySection($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validated = $validator->validated();

        $assignments = SectionSubject::with(['subject', 'employee'])
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->get();

        return response()->json([
            'assigned_subjects' => $assignments,
        ]);
    }





}
