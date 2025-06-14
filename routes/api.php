<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\FeeController;

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
// Student routes End


// Fee Slip routes Start
Route::post('add-fee-slips', [FeeController::class, 'addFeeSlips']);
Route::get('fee-slip-by-challan/{challan_no}', [FeeController::class, 'getFeeSlipByChallan']);
Route::post('pay-fee-slip', [FeeController::class, 'payFeeSlip']);