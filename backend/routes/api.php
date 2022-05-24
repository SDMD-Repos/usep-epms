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
    Route::post('/update-function-default-program/{id}', 'SettingController@updateProgramFunction');

    Route::get('/get-all-programs/{year}', 'SettingController@getPrograms');
    Route::post('/create-program', 'SettingController@createProgram');
    Route::post('/delete-program/{id}', 'SettingController@deleteProgram');

    Route::get('/get-other-programs/{year}/{form_id}', 'SettingController@getOtherPrograms');
    Route::post('/create-other-program', 'SettingController@createOtherProgram');
    Route::post('/delete-other-program/{id}', 'SettingController@deleteOtherProgram');

    Route::get('/get-sub-categories/{year}/{isNested}', 'SettingController@getSubCategories');
    Route::post('/create-sub-category', 'SettingController@createSubCategory');
    Route::post('/delete-sub-category/{id}', 'SettingController@deleteSubCategory');
    Route::get('/get-all-measures/{year}', 'SettingController@getMeasures');
    Route::post('/create-measure', 'SettingController@createMeasure');
    Route::post('/update-measure/{id}', 'SettingController@updateMeasure');
    Route::post('/delete-measure/{id}', 'SettingController@deleteMeasure');

    Route::get('/get-user-form-access/{pmaps_id}', 'SettingController@getUserFormAccess');
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

    Route::get('/get-all-form-fields/{year}/{formId}', 'SettingController@getAllFormFields');
    Route::post('/save-form-field-settings', 'SettingController@saveFormFieldSettings');
    Route::post('/update-form-field-settings/{id}', 'SettingController@updateFormFieldSettings');

    Route::post('/get-all-spms-forms-permission', 'SettingController@getAllFormsByPermission');
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
        Route::get('/viewPdf/{id}', 'VpopcrController@viewOpcrVpPdf');
    });

    # OPCR & CPCR Form Controller routes

    Route::group([
        'prefix' => 'ocpcr'
    ], function() {
        Route::get('/check-saved/{officeId}/{year}', 'Form\VpopcrController@checkSaved');
        Route::get('/check-saved-template/{year}', 'Form\OcpcrController@checkSaved');
        Route::get('/get-vp-opcr-details/{officeId}/{year}/{formId}', 'Form\OcpcrController@getVpOpcrDetails');
        Route::post('/save', 'Form\VpopcrController@save');
        Route::get('/list', 'Form\VpopcrController@getAllVpOpcrs');
        Route::post('/publish', 'Form\VpopcrController@publish');
        Route::post('/deactivate', 'Form\VpopcrController@deactivate');
        Route::get('/view/{id}', 'Form\VpopcrController@view');
        Route::get('/viewPdf/{id}', 'AppController@viewVpOpcrPdf');
        Route::post('/update/{id}', 'Form\VpopcrController@update');
    });

    Route::group([
        'prefix' => 'opcr'
    ], function() {

        //GET

        //TEMPLATE
        Route::get('/check-saved-template/{year}', 'Form\OpcrController@checkSavedTemplate');
        Route::get('/view-template/{id}', 'Form\OpcrController@viewTemplate');
        Route::get('/template-list', 'Form\OpcrController@getAllOpcrTemplate');
        //OPCR
        Route::get('/check-saved/{year}', 'Form\OpcrController@checkSaved');

        //POST
        Route::post('/save-template', 'Form\OpcrController@saveTemplate');
        Route::post('/publish-template', 'Form\OpcrController@publishTemplate');
        Route::post('/deactivate-template', 'Form\OpcrController@deactivateTemplate');
        Route::post('/update-template/{id}', 'Form\OpcrController@updateTemplate');
        Route::post('/unpublish-template', 'Form\OpcrController@unpublishTemplate');

    });

});

Route::group([
    'prefix' => 'system',
    'middleware' => 'auth:api'
], function() {

    # Access Permission
    Route::get('/permission', 'SystemAdmin\PermissionController@detailsPermission');
    Route::post('/save-permission', 'SystemAdmin\PermissionController@savePermission');
    Route::get('/get-permission-by-user/{id}', 'SystemAdmin\PermissionController@fetchPermissionByUser');
    Route::post('/update-permission', 'SystemAdmin\PermissionController@updatePermission');
    Route::post('/save-office-head','SystemAdmin\PermissionController@saveOfficeHead');
    Route::get('/fetch-office-head/{form_id}/{office_id}','SystemAdmin\PermissionController@fetchOfficeHead');
    Route::post('/save-office-staff','SystemAdmin\PermissionController@saveOfficeStaff');
    Route::post('/check-access', 'SystemAdmin\PermissionController@checkAccessByPermissions');
    Route::get('/check-form-head/{pmaps_id}/{form_id}', 'SystemAdmin\PermissionController@checkFormHead');
    Route::get('/allow-form/{pmaps_id}/{form_id}', 'SystemAdmin\PermissionController@allowForm');

    Route::group([
        'prefix' => 'requests'
    ], function() {
        Route::get('/get-all-unpublish/{status}', 'SystemAdmin\RequestsController@getAllUnpublishRequests');
        Route::post('/update-request-status', 'SystemAdmin\RequestsController@updateFormRequestStatus');
        Route::get('/view-unpublished-form/{id}', 'SystemAdmin\RequestsController@viewUnpublishedForm');
    });
});

Route::group([
    'prefix' => 'hris',
    'middleware' => 'auth:api'
], function() {
    Route::post('/get-main-offices-children', 'SettingController@getMainOfficesWithChildren');
    Route::get('/get-vp-offices-children', 'SettingController@getVpOfficeWithChildren');
    Route::get('/get-main-offices-only/{officesOnly}', 'SettingController@getMainOfficesOnly');
    Route::get('/get-personnel-by-office/{id}', 'SettingController@getPersonnelByOffice');
    Route::get('/get-all-positions', 'SettingController@getAllPositions');
    Route::get('/get-user-offices/{formId}', 'SettingController@getUserOffices');
    Route::post('/get-offices-accountable', 'SettingController@getOfficesAccountable');

  
});
