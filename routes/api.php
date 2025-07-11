<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\FeeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AcademicController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ExamController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//class routes
/* Route::prefix('classes')->group(function () {
    Route::get('/', [ClassController::class, 'index'])->name('classes.index');
    Route::post('/add', [ClassController::class, 'addClass'])->name('classes.add');
    Route::put('/update/{id}', [ClassController::class, 'updateClass'])->name('classes.update');
    Route::delete('/delete/{id}', [ClassController::class, 'deleteClass'])->name('classes.delete');
}); */




//Class-Section routes Start
Route::post('section/add', [ClassController::class, 'addSection']);
Route::post('class/add', [ClassController::class, 'addClass']);
Route::get('sections/get', [ClassController::class, 'getSections']);
Route::get('classes/get', [ClassController::class, 'getClasses']);
Route::get('class/get/by-id/{id}', [ClassController::class, 'getClassByID']);
Route::get('section/get/by-id/{id}', [ClassController::class, 'getSectionByID']);
Route::get('sections/get/by-classID/{id}', [ClassController::class, 'getSectionsByClassID']);
//Class-Section routes End



// Student routes Start
Route::post('student/add', [StudentController::class, 'addStudent']);
Route::get('students/get/pending', [StudentController::class, 'getPendingStudents']);
Route::post('student/guardian/add', [StudentController::class, 'addGuardian']);
Route::post('student/extra/add', [StudentController::class, 'addExtra']);
Route::post('student/enrollment/add', [StudentController::class, 'addEnrollment']);
Route::get('student/get/by-id/{id}', [StudentController::class, 'getStudentById']);
Route::get('student/get/by-reg/{reg_number}', [StudentController::class, 'getStudentByRegNumber']);
Route::get('student/guardian/get/by-studentID/{student_id}', [StudentController::class, 'getGuardianByStudentID']);
Route::post('students/get/by-section', [StudentController::class, 'getStudentsByClassAndSection']);

Route::get('student/account/{student_id}', [StudentController::class, 'getAccountWithFeeStructure']);
Route::post('student/account/history/slip', [StudentController::class, 'getFeeHistory']);
Route::post('student/account/history', [StudentController::class, 'getMonthlyStatus']);
Route::post('student/account/update', [StudentController::class, 'updateStudentAccount']);

Route::post('student/attendance/by-date', [StudentController::class, 'getAttendanceByDate']);
Route::post('student/attendance/monthly', [StudentController::class, 'getMonthlyAttendance']);

Route::post('student/results/by-term', [StudentController::class, 'getResultsByTerm']);

// Student routes End


// Attendance routes Start
Route::post('attendance/store', [AttendanceController::class, 'store']);
Route::post('attendance/by-date', [AttendanceController::class, 'getByDate']);
Route::post('attendance/records/by-id', [AttendanceController::class, 'recordsByAttendanceId']);
// Attendance routes End


// Accounts routes Start

Route::post('fee/structure/add', [FeeController::class, 'addFeeStructure']);
Route::get('fee/structures/get', [FeeController::class, 'getFeeStructures']);
Route::post('fee/challan/pay', [FeeController::class, 'payFeeSlip']);
Route::post('fee/challans/add', [FeeController::class, 'addFeeSlips']);
Route::post('fee/challans/by-class', [FeeController::class, 'getFeeSlips']);
Route::post('fee/challans/expire', [FeeController::class, 'expireFeeSlips']);
Route::get('fee/challan/get/{challan_no}', [FeeController::class, 'getFeeSlipByChallan']);
Route::post('fee/summary/get', [FeeController::class, 'getFeesSummary']);

//Route::get('pending-student/{id}', [StudentController::class, 'getPendingStudentById']);

// Accounts routes End

// Academic routes Start
Route::post('subject/add', [AcademicController::class, 'addSubject']);
Route::get('subject/get', [AcademicController::class, 'getSubjects']);
Route::post('subject/assignment/add', [AcademicController::class, 'subjectAssignment']);
Route::post('subject/assignment/get', [AcademicController::class, 'getAssignedSubjectsBySection']);
// Academic routes End

// Employee routes Start
Route::post('employee/add', [EmployeeController::class, 'addEmployee']);
Route::get('employees/get', [EmployeeController::class, 'getEmployees']);
Route::get('employees/get-pending', [EmployeeController::class, 'getPendingEmployees']);
Route::post('employee/qualification/add', [EmployeeController::class, 'addQualification']);
Route::post('employee/qualification/get', [EmployeeController::class, 'getQualifications']);
Route::get('teachers/get', [EmployeeController::class, 'getTeachers']);

Route::post('employee/incharge/assign', [EmployeeController::class, 'assignInchargeToSection']);
Route::get('employee/incharge/get/all', [EmployeeController::class, 'getIncharge']);
Route::get('employee/incharge/get/{employee_id}', [EmployeeController::class, 'getInchargeByEmployeeId']);
Route::get('employee/subjects/get/{employee_id}', [EmployeeController::class, 'getSubjectsByEmployeeId']);
// Employee routes End


// Exam routes Start
Route::post('exam/add', [ExamController::class, 'store']);
Route::post('exam/get', [ExamController::class, 'index']);
Route::post('exam/records/by-id', [ExamController::class, 'getResultRecordsByResultId']);

