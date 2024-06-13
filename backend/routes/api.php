<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UserController,
    SettingController,
    AppController
};
use App\Http\Controllers\Form\{
    AapcrController,
    VpopcrController,
    OcpcrController,
    TemplateController
};

use App\Http\Controllers\SystemAdmin\{
    PermissionController,
    RequestsController
};

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

Route::group([
    'prefix' => 'auth'
], function () {
    Route::get('/login', [UserController::class, 'login'])->name("login");
    Route::group([
        'middleware' => ['cors', 'json.response']
    ], function () {

        Route::post('/login', [UserController::class, 'login']);
    });

    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('/logout', [UserController::class, 'logout']);
        Route::get('/account', [UserController::class, 'account']);
    });
});

Route::group([
    'prefix' => 'settings',
    'middleware' => 'auth:api'
], function () {
    Route::get('/get-all-functions/{year}/{formId}', [SettingController::class, 'getFunctions']);
    Route::post('/create-function', [SettingController::class, 'createFunction']);
    Route::post('/delete-category/{id}', [SettingController::class, 'deleteCategory']);

    Route::post('/update-function-default-program/{id}', [SettingController::class, 'updateProgramFunction']);
    Route::post('/save-form-category', [SettingController::class, 'saveFormCategory']);
    Route::post('/delete-form-category/{id}', [SettingController::class, 'deleteFormCategory']);

    Route::get('/get-all-programs/{year}/{formId}', [SettingController::class, 'getPrograms']);
    Route::post('/create-program', [SettingController::class, 'createProgram']);
    Route::post('/delete-program/{id}', [SettingController::class, 'deleteProgram']);

    Route::get('/get-other-programs/{year}/{form_id}', [SettingController::class, 'getOtherPrograms']);
    Route::post('/create-other-program', [SettingController::class, 'createOtherProgram']);
    Route::post('/delete-other-program/{id}', [SettingController::class, 'deleteOtherProgram']);

    Route::get('/get-sub-categories/{year}/{isNested}', [SettingController::class, 'getSubCategories']);
    Route::post('/create-sub-category', [SettingController::class, 'createSubCategory']);
    Route::post('/update-sub-category', [SettingController::class, 'updateSubCategory']);
    Route::post('/delete-sub-category/{id}', [SettingController::class, 'deleteSubCategory']);
    Route::get('/get-all-measures/{year}', [SettingController::class, 'getMeasures']);
    Route::post('/create-measure', [SettingController::class, 'createMeasure']);
    Route::post('/update-measure/{id}', [SettingController::class, 'updateMeasure']);
    Route::post('/delete-measure/{id}', [SettingController::class, 'deleteMeasure']);
    Route::get('/get-all-measure-ratings/{year}', [SettingController::class, 'getMeasureRatings']);
    Route::post('/create-measure-rating', [SettingController::class, 'validateMeasureRating']);
    Route::post('/update-measure-rating/{id}', [SettingController::class, 'updateMeasureRating']);
    Route::post('/delete-measure-rating/{id}', [SettingController::class, 'deleteMeasureRating']);

    Route::get('/view-measure-pdf/{year}', [AppController::class, 'viewMeasurePDF']);

    Route::get('/get-user-form-access', [SettingController::class, 'getUserFormAccess']);
    Route::get('/get-all-spms-forms', [SettingController::class, 'getAllForms']);

    Route::get('/get-all-signatory-types', [SettingController::class, 'getAllSignatoryTypes']);

    Route::get('/get-year-signatories/{year}/{formId}/{officeId}', [SettingController::class, 'getYearSignatories']);
    Route::post('/save-signatories', [SettingController::class, 'saveSignatories']);
    Route::post('/update-signatories', [SettingController::class, 'updateSignatories']);
    Route::post('/delete-signatory/{id}', [SettingController::class, 'deleteSignatory']);

    Route::get('/get-all-groups', [SettingController::class, 'getAllGroups']);
    Route::post('/create-group', [SettingController::class, 'saveGroup']);
    Route::post('/update-group/{id}', [SettingController::class, 'updateGroup']);
    Route::post('/delete-group/{id}', [SettingController::class, 'deleteGroup']);

    Route::get('/get-all-cascading-levels', [SettingController::class, 'getAllCascadingLevels']);

    Route::get('/get-all-form-fields/{year}/{formId}', [SettingController::class, 'getAllFormFields']);
    Route::post('/save-form-field-settings', [SettingController::class, 'saveFormFieldSettings']);
    Route::post('/update-form-field-settings/{id}', [SettingController::class, 'updateFormFieldSettings']);

    Route::post('/get-all-spms-forms-permission', [SettingController::class, 'getAllFormsByPermission']);
});

Route::group([
    'prefix' => 'forms',
    'middleware' => 'auth:api'
], function () {

    Route::get('/viewSavedPdf/{fileName}', [AppController::class, 'viewSavedPdf']);

    # AAPCR Controller routes

    Route::group([
        'prefix' => 'aapcr',
    ], function () {
        Route::post('/save', [AapcrController::class, 'save']);
        Route::get('/check-saved/{year}', [AapcrController::class, 'checkSaved']);
        Route::get('/list', [AapcrController::class, 'getAllAapcrs']);
        Route::post('/publish', [AapcrController::class, 'publish']);
        Route::post('/unpublish', [AapcrController::class, 'unpublish']);
        Route::post('/deactivate', [AapcrController::class, 'deactivate']);
        Route::get('/view/{id}', [AapcrController::class, 'view']);
        Route::get('/viewPdf/{id}', [AppController::class, 'viewAapcrPdf']);
        Route::post('/update/{id}', [AapcrController::class, 'update']);
        Route::get('/print/pdf/{id}', [AapcrController::class, "print_aapcr"]);
    });

    # OPCR (VP) Controller routes

    Route::group([
        'prefix' => 'opcrvp'
    ], function () {
        Route::get('/check-saved/{officeId}/{year}', [VpopcrController::class, 'checkSaved']);
        Route::get('/get-aapcr-details/{vpId}/{year}', [VpopcrController::class, 'getAapcrDetails']);
        Route::post('/save', [VpopcrController::class, 'save']);
        Route::get('/list', [VpopcrController::class, 'getAllVpOpcrs']);
        Route::post('/publish', [VpopcrController::class, 'publish']);
        Route::post('/unpublish', [VpopcrController::class, 'unpublish']);
        Route::post('/deactivate', [VpopcrController::class, 'deactivate']);
        Route::get('/view/{id}', [VpopcrController::class, 'view']);
        Route::get('/viewPdf/{id}', [AppController::class, 'viewVpOpcrPdf']);
        Route::post('/update/{id}', [VpopcrController::class, 'update']);
        Route::post('/check-saved-indicators', [VpopcrController::class, 'checkSavedIndicators']);
    });

    # OPCR & CPCR Form Controller routes
    Route::group([
        'prefix' => 'ocpcr'
    ], function () {

        # MAIN FORM
        Route::get('/check-saved/{officeId}/{year}', [OcpcrController::class, 'checkSaved']);
        Route::get('/get-vp-opcr-details/{officeId}/{year}/{formId}', [OcpcrController::class, 'getVpOpcrDetails']);
        Route::get('/list', [OcpcrController::class, 'getAllOpcr']);

        # TEMPLATE
        Route::group([
            'prefix' => 'template'
        ], function () {
            Route::get('/check-saved/{year}', [TemplateController::class, 'opcrCheckSaved']);
            Route::post('/save', [TemplateController::class, 'saveOpcr']);
            Route::get('/list', [TemplateController::class, 'getAllOpcr']);
            Route::get('/view/{id}', [TemplateController::class, 'viewOpcr']);
            Route::post('/update/{id}', [TemplateController::class, 'updateOpcr']);

            Route::post('/publish', [TemplateController::class, 'publishOpcr']);
            Route::post('/deactivate', [TemplateController::class, 'deactivateOpcr']);
            Route::post('/unpublish', [TemplateController::class, 'unpublishOpcr']);
        });
    });
});

Route::group([
    'prefix' => 'system',
    'middleware' => 'auth:api'
], function () {

    # Access Permission
    Route::get('/permission', [PermissionController::class, 'detailsPermission']);
    Route::post('/save-permission', [PermissionController::class, 'savePermission']);
    Route::get('/get-permission-by-user/{id}', [PermissionController::class, 'fetchPermissionByUser']);
    Route::post('/update-permission', [PermissionController::class, 'updatePermission']);
    Route::post('/save-office-head', [PermissionController::class, 'saveOfficeHead']);
    Route::get('/fetch-office-head/{form_id}/{office_id}', [PermissionController::class, 'fetchOfficeHead']);
    Route::post('/save-office-staff', [PermissionController::class, 'saveOfficeStaff']);
    Route::post('/check-access', [PermissionController::class, 'checkAccessByPermissions']);
    Route::get('/check-form-head/{pmaps_id}/{form_id}', [PermissionController::class, 'checkFormHead']);
    Route::get('/check-form-access/{pmaps_id}/{form_id}', [PermissionController::class, 'checkFormAccess']);

    Route::group([
        'prefix' => 'requests'
    ], function () {
        Route::get('/get-all-unpublish/{status}', [RequestsController::class, 'getAllUnpublishRequests']);
        Route::post('/update-request-status', [RequestsController::class, 'updateFormRequestStatus']);
        Route::get('/view-unpublished-form/{id}', [RequestsController::class, 'viewUnpublishedForm']);
    });
});

Route::group([
    'prefix' => 'hris',
    'middleware' => 'auth:api'
], function () {
    Route::post('/get-main-offices-children', [SettingController::class, 'getMainOfficesWithChildren']);
    Route::get('/get-main-offices-only/{officesOnly}', [SettingController::class, 'getMainOfficesOnly']);
    Route::get('/get-personnel-by-office/{id}/{permanentOnly}/{isSubunit}', [SettingController::class, 'getPersonnelByOffice']);
    Route::get('/get-all-positions', [SettingController::class, 'getAllPositions']);
    Route::get('/get-user-offices/{formId}', [SettingController::class, 'getUserOffices']);
    Route::post('/get-offices-accountable', [SettingController::class, 'getOfficesAccountable']);
});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
