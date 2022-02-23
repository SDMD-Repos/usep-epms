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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::group([
    'prefix' => 'auth'
], function () {

    Route::group([
        'middleware' => ['cors', 'json.response']
    ], function () {
        Route::post('/login', 'UserController@login');
    });

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
    Route::get('/get-all-functions/{year}', 'SettingController@getFunctions');
    Route::post('/create-function', 'SettingController@createFunction');
    Route::post('/delete-category/{id}', 'SettingController@deleteCategory');

    Route::get('/get-all-programs', 'SettingController@getPrograms');
    Route::post('/create-program', 'SettingController@createProgram');
    Route::post('/delete-program/{id}', 'SettingController@deleteProgram');

    Route::get('/get-sub-categories', 'SettingController@getSubCategories');
    Route::post('/create-sub-category', 'SettingController@createSubCategory');
    Route::post('/delete-sub-category/{id}', 'SettingController@deleteSubCategory');

    Route::get('/get-all-measures/{year}', 'SettingController@getMeasures');
    Route::post('/create-measure', 'SettingController@createMeasure');
    Route::post('/update-measure/{id}', 'SettingController@updateMeasure');
    Route::post('/delete-measure/{id}', 'SettingController@deleteMeasure');

    Route::get('/get-all-spms-forms', 'SettingController@getAllForms');

    Route::get('/get-all-signatory-types', 'SettingController@getAllSignatoryTypes');

    Route::get('/get-year-signatories/{year}/{formId}/{officeId}', 'SettingController@getYearSignatories');
    Route::post('/save-signatories', 'SettingController@saveSignatories');
    Route::post('/update-signatories', 'SettingController@updateSignatories');
    Route::post('/delete-signatory/{id}', 'SettingController@deleteSignatory');

    Route::get('/get-all-groups', 'SettingController@getAllGroups');
    Route::post('/create-group', 'SettingController@saveGroup');
    Route::post('/update-group/{id}', 'SettingController@updateGroup');
    Route::post('/delete-group/{id}', 'SettingController@deleteGroup');

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
        Route::get('/check-saved/{year}', 'Form\AapcrController@checkSaved');
        Route::get('/list', 'Form\AapcrController@getAllAapcrs');
        Route::post('/publish', 'Form\AapcrController@publish');
        Route::post('/unpublish', 'Form\AapcrController@unpublish');
        Route::post('/deactivate', 'Form\AapcrController@deactivate');
        Route::get('/view/{id}', 'Form\AapcrController@view');
        Route::get('/viewPdf/{id}', 'AppController@viewAapcrPdf');
        Route::post('/update/{id}', 'Form\AapcrController@update');
        Route::get('/viewUploadedFile/{id}', 'Form\AapcrController@viewUploadedFile');
        Route::post('/update-file', 'Form\AapcrController@updateFile');
    });

    # OPCR (VP) Controller routes

    Route::group([
        'prefix' => 'opcrvp'
    ], function() {
        Route::get('/check-saved/{officeId}/{year}', 'Form\VpopcrController@checkSaved');
        Route::get('/get-aapcr-details/{vpId}/{year}', 'Form\VpopcrController@getAapcrDetails');
        Route::post('/save', 'Form\VpopcrController@save');
        Route::get('/list', 'Form\VpopcrController@getAllVpOpcrs');
        Route::post('/publish', 'Form\VpopcrController@publish');
        Route::post('/unpublish', 'Form\VpopcrController@unpublish');
        Route::post('/deactivate', 'Form\VpopcrController@deactivate');
        Route::get('/view/{id}', 'Form\VpopcrController@view');
        Route::get('/viewPdf/{id}', 'AppController@viewVpOpcrPdf');
        Route::post('/update/{id}', 'Form\VpopcrController@update');
        Route::get('/viewUploadedFile/{id}', 'Form\VpopcrController@viewUploadedFile');
        Route::post('/update-file', 'Form\VpopcrController@updateFile');
    });

    # OPCR & CPCR Form Controller routes

    Route::group([
        'prefix' => 'ocpcr'
    ], function() {
        Route::get('/check-saved/{officeId}/{year}', 'Form\VpopcrController@checkSaved');
        Route::get('/get-vp-opcr-details/{officeId}/{year}/{formId}', 'Form\OcpcrController@getVpOpcrDetails');
        Route::post('/save', 'Form\VpopcrController@save');
        Route::get('/list', 'Form\VpopcrController@getAllVpOpcrs');
        Route::post('/publish', 'Form\VpopcrController@publish');
        Route::post('/deactivate', 'Form\VpopcrController@deactivate');
        Route::get('/view/{id}', 'Form\VpopcrController@view');
        Route::get('/viewPdf/{id}', 'AppController@viewVpOpcrPdf');
        Route::post('/update/{id}', 'Form\VpopcrController@update');
    });
});

Route::group([
    'prefix' => 'hris',
    'middleware' => 'auth:api'
], function() {
    Route::get('/get-main-offices-children/{nodeStatus}', 'SettingController@getMainOfficesWithChildren');
    Route::get('/get-main-offices-only/{officesOnly}', 'SettingController@getMainOfficesOnly');
    Route::get('/get-personnel-by-office/{id}', 'SettingController@getPersonnelByOffice');
    Route::get('/get-all-positions', 'SettingController@getAllPositions');
    Route::get('/get-user-offices/{formId}', 'SettingController@getUserOffices');
    Route::get('/get-offices-accountable/{nodeStatus}', 'SettingController@getOfficesAccountable');
});
