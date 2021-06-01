<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('/login', 'UserController@login');

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('/logout', 'UserController@logout');
        Route::get('/account', 'UserController@account');
    });
});

Route::group([
    'prefix' => 'settings',
    'middleware' => 'auth:api'
], function () {
    Route::get('/get-all-functions', 'SettingController@getFunctions');
    Route::post('/create-function', 'SettingController@createFunction');
    Route::post('/delete-category/{id}', 'SettingController@deleteCategory');

    Route::get('/get-all-programs', 'SettingController@getPrograms');
    Route::post('/create-program', 'SettingController@createProgram');
    Route::post('/delete-program/{id}', 'SettingController@deleteProgram');

    Route::get('/get-sub-categories', 'SettingController@getSubCategories');
    Route::post('/create-sub-category', 'SettingController@createSubCategory');
    Route::post('/delete-sub-category/{id}', 'SettingController@deleteSubCategory');

    Route::get('/get-all-measures', 'SettingController@getMeasures');
    Route::post('/create-measure', 'SettingController@createMeasure');
    Route::post('/update-measure/{id}', 'SettingController@updateMeasure');
    Route::post('/delete-measure/{id}', 'SettingController@deleteMeasure');

    Route::get('/get-all-spms-forms', 'SettingController@getAllForms');

    Route::get('/get-all-positions', 'SettingController@getAllPositions');

    Route::get('/get-year-signatories/{year}/{formId}', 'SettingController@getYearSignatories');
    Route::post('/save-signatories', 'SettingController@saveSignatories');
    Route::post('/update-signatories', 'SettingController@updateSignatories');
    Route::post('/delete-signatory/{id}', 'SettingController@deleteSignatory');

    Route::get('/get-all-cascading-levels', 'SettingController@getAllCascadingLevels');
});

Route::group([
    'prefix' => 'forms',
    'middleware' => 'auth:api'
], function() {

    # AAPCR Controller routes

    Route::group([
        'prefix' => 'aapcr',
    ], function() {
        Route::post('/save', 'Form\AapcrController@save');
    });
});

Route::group([
    'prefix' => 'hris',
    'middleware' => 'auth:api'
], function() {
    Route::get('/get-main-offices/{nodeStatus}', 'SettingController@getMainOffices');
    Route::get('/get-personnel-by-office/{id}', 'SettingController@getPersonnelByOffice');
});
