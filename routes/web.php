<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard-breeze', function () {
    return view('dashboard');
})->middleware(['auth']);


Route::middleware(['auth'])->group(function ()
{
    // Get data to view for person as dashboard
Route::get('/dashboard' ,[DashboardController::class,'index']);
// resource 
Route::resource('/expenses',ExpenseController::class);
Route::resource('/categories',CategoryController::class);
Route::resource('/budgets',BudgetController::class);
// Get the data 
Route::get('/reports',[ReportController::class,'index']);
Route::get('/export/csv' ,[ReportController::class,'exportCSV']);
Route::get('/export/pdf' ,[ReportController::class,'exportPDF']);


});






require __DIR__.'/auth.php';



