<?php

use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Http\Controllers\Form\AapcrController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // $category = Category::find(1963);
    // return count((array)$category);
    return view('welcome');
});

Route::get('/print/aapcr', [AapcrController::class, "print_aapcr"]);
