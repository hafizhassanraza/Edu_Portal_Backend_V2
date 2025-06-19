<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\FeeController;
use App\Http\Controllers\Api\AttendanceController;

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
Route::post('add-section', [ClassController::class, 'addSection']);
Route::post('add-class', [ClassController::class, 'addClass']);
Route::get('sections', [ClassController::class, 'getSections']);
Route::get('classes', [ClassController::class, 'getClasses']);
Route::get('class-by-id/{id}', [ClassController::class, 'getClassByID']);
Route::get('section-by-id/{id}', [ClassController::class, 'getSectionByID']);
Route::get('sections-by-classID/{id}', [ClassController::class, 'getSectionsByClassID']);
//Class-Section routes End


// Student routes Start
Route::post('add-student', [StudentController::class, 'addStudent']);
Route::get('pending-students', [StudentController::class, 'getPendingStudents']);
Route::get('guardian-by-studentID/{student_id}', [StudentController::class, 'getGuardianByStudentID']);
Route::post('add-guardian', [StudentController::class, 'addGuardian']);
Route::post('add-enrollment', [StudentController::class, 'addEnrollment']);
Route::post('add-extra', [StudentController::class, 'addExtra']);
Route::post('students-by-section', [StudentController::class, 'getStudentsByClassAndSection']);
Route::get('student-by-reg-number/{reg_number}', [StudentController::class, 'getStudentByRegNumber']);
Route::get('student-by-id/{id}', [StudentController::class, 'getStudentById']);
// Student routes End


// Attendance routes Start
Route::post('attendance/store', [AttendanceController::class, 'store']);
Route::post('attendance/by-date', [AttendanceController::class, 'getByDate']);

// Attendance routes End


// Accounts routes Start
Route::post('add-fee-slips', [FeeController::class, 'addFeeSlips']);
Route::get('fee-slip-by-challan/{challan_no}', [FeeController::class, 'getFeeSlipByChallan']);
Route::post('pay-fee-slip', [FeeController::class, 'payFeeSlip']);
Route::post('add-fee-structure', [FeeController::class, 'addFeeStructure']);
Route::get('fee-structures', [FeeController::class, 'getFeeStructures']);
Route::post('get-Fee-Slips', [FeeController::class, 'getFeeSlips']);
Route::post('fees-summary', [FeeController::class, 'getFeesSummary']);
Route::post('expire-Fee-Slips', [FeeController::class, 'expireFeeSlips']);
Route::get('pending-student/{id}', [StudentController::class, 'getPendingStudentById']);


