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


class AttendanceController extends Controller
{
    


    public function index(Request $request)
    {
        $query = Attendance::with(['records', 'records.student']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        if ($request->has('section_id')) {
            $query->where('section_id', $request->input('section_id'));
        }
        if ($request->has('date')) {
            $query->where('date', $request->input('date'));
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'attendances' => $attendances
        ]);
    }



    public function recordsByAttendanceId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:attendances,id',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $attendance = Attendance::with(['attendanceRecords', 'students'])->find($request->input('id'));

        return response()->json([
            'attendance' => $attendance,
            'records' => $attendance ,
        ]);
    }



    public function getByDate(Request $request)
    {
        $validator = $this->validateGetByDate($request);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::where('date', $request->input('date'))->get();

        $attendance = $attendance->map(function ($att) {
            $present = AttendanceRecord::where('attendance_id', $att->id)->where('status', 'present')->count();
            $absent = AttendanceRecord::where('attendance_id', $att->id)->where('status', 'absent')->count();
            $leave = AttendanceRecord::where('attendance_id', $att->id)->where('status', 'leave')->count();
            $total = AttendanceRecord::where('attendance_id', $att->id)->count();

            $att->present = $present;
            $att->absent = $absent;
            $att->leave = $leave;
            $att->total = $total;
            return $att;
        });

        return response()->json([
            'attendances' => $attendance,
        ]);
    }



     public function store(Request $request)
    {
        $validator = $this->validateAttendance($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);
        $data = $request->all();


        $attendance = Attendance::firstOrNew([
            'date' => $data['date'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'],
        ]);

        if ($attendance->exists) AttendanceRecord::where('attendance_id', $attendance->id)->delete();
        else $attendance->save();

        $createdRecords = [];
        foreach ($data['details'] as $detail) {
            $createdRecords[] = AttendanceRecord::create([
                'attendance_id' => $attendance->id,
                'student_id' => $detail['student_id'],
                'status' => $detail['status'],
            ]);
        }

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'attendance_id' => $attendance->id,
            'records' => $createdRecords,
        ], 201);
    }



    // Validator function for getByDate
    protected function validateGetByDate(Request $request)
    {
        return Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
        ], [
            'date.required' => 'The attendance date is required.',
            'date.date_format' => 'The attendance date must be in the format Y-m-d (e.g., 2025-12-05).',
        ]);
    }


    // Validator function for attendance
    protected function validateAttendance(Request $request)
    {
        return Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'details' => 'required|array',
            'details.*.student_id' => 'required|integer',
            'details.*.status' => 'required|string',
        ], [
            'date.required' => 'The attendance date is required.',
            'date.date_format' => 'The attendance date must be in the format Y-m-d (e.g., 2025-12-05).',
            'class_id.required' => 'The class is required.',
            'class_id.integer' => 'The class ID must be an integer.',
            'section_id.required' => 'The section is required.',
            'section_id.integer' => 'The section ID must be an integer.',
            'details.required' => 'Attendance details are required.',
            'details.array' => 'Attendance details must be an array.',
            'details.*.student_id.required' => 'Each attendance record must have a student ID.',
            'details.*.student_id.integer' => 'Each student ID must be an integer.',
            'details.*.status.required' => 'Each attendance record must have a status.',
            'details.*.status.string' => 'Each status must be a string.',
        ]);
    }



}
