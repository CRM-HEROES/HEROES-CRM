<?php

use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\DefaultFieldController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\KavkomController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WebserviceController;

use App\Http\Controllers\API\Google\AuthenticatorController as GoogleAuthenticatorController;
use App\Http\Controllers\API\Google\CalendarController as GoogleCalendarController;
use App\Http\Controllers\API\Google\ContactController as GoogleContactController;

use App\Http\Controllers\API\PermissionController as PermissionController;

use App\Http\Controllers\API\Project\API\PappersController as ProjectPappersController;

use App\Http\Controllers\API\Project\CalendarController as ProjectCalendarController;
use App\Http\Controllers\API\Project\CategoryController as ProjectCategoryController;
use App\Http\Controllers\API\Project\CommissionController as ProjectCommissionController;

use App\Http\Controllers\API\Project\CampaignController as ProjectCampaignController;
use App\Http\Controllers\API\Project\Campaign\ActionController as ProjectCampaignActionController;
use App\Http\Controllers\API\Project\Campaign\CampaignActionController as ProjectCampaignCampaignActionController;
use App\Http\Controllers\API\Project\Campaign\CampaignOperatorController as ProjectCampaignCampaignOperatorController;
use App\Http\Controllers\API\Project\Campaign\CampaignRuleController as ProjectCampaignCampaignRuleController;
use App\Http\Controllers\API\Project\Campaign\OperatorController as ProjectCampaignOperatorController;
use App\Http\Controllers\API\Project\Campaign\OperatorOperatorController as ProjectCampaignOperatorOperatorController;
use App\Http\Controllers\API\Project\Campaign\OperatorRuleController as ProjectCampaignOperatorRuleController;
use App\Http\Controllers\API\Project\Campaign\ProspectController as ProjectCampaignProspectController;
use App\Http\Controllers\API\Project\Campaign\OrderController as ProjectCampaignOrderController;
use App\Http\Controllers\API\Project\Campaign\RuleController as ProjectCampaignRuleController;

use App\Http\Controllers\API\Project\DefaultMenuController as ProjectDefaultMenuController;

use App\Http\Controllers\API\Project\DocumentController as ProjectDocumentController;
use App\Http\Controllers\API\Project\DuplicateController as ProjectDuplicateController;
use App\Http\Controllers\API\Project\Document\FontController as ProjectDocumentFontController;
use App\Http\Controllers\API\Project\Document\FieldController as ProjectDocumentFieldController;
use App\Http\Controllers\API\Project\Document\FileController as ProjectDocumentFileController;
use App\Http\Controllers\API\Project\Document\PageController as ProjectDocumentPageController;
use App\Http\Controllers\API\Project\DocumentTemplateController as ProjectDocumentTemplateController;

use App\Http\Controllers\API\Project\EventController as ProjectEventController;
use App\Http\Controllers\API\Project\EventDescriptionTemplateController as ProjectEventDescriptionTemplateController;
use App\Http\Controllers\API\Project\ExportController as ProjectExportController;
use App\Http\Controllers\API\Project\FolderController as ProjectFolderController;
use App\Http\Controllers\API\Project\FieldController as ProjectFieldController;
use App\Http\Controllers\API\Project\GroupController as ProjectGroupController;

use App\Http\Controllers\API\Project\Google\DriveController as ProjectGoogleDriveController;

use App\Http\Controllers\API\Project\ImportController as ProjectImportController;
use App\Http\Controllers\API\Project\Import\DuplicateController as ProjectImportDuplicateController;
use App\Http\Controllers\API\Project\Import\GroupController as ProjectImportGroupController;
use App\Http\Controllers\API\Project\Import\LabelController as ProjectImportLabelController;
use App\Http\Controllers\API\Project\Import\MappingController as ProjectImportMappingController;
use App\Http\Controllers\API\Project\Import\UserController as ProjectImportUserController;

use App\Http\Controllers\API\Project\LabelController as ProjectLabelController;
use App\Http\Controllers\API\Project\LogoController as ProjectLogoController;
use App\Http\Controllers\API\Project\MenuController as ProjectMenuController;
use App\Http\Controllers\API\Project\MenuIconController as ProjectMenuIconController;
use App\Http\Controllers\API\Project\MessageTemplateController as ProjectMessageTemplateController;
use App\Http\Controllers\API\Project\OcrController as ProjectOcrController;

use App\Http\Controllers\API\Project\OrderController as ProjectOrderController;
use App\Http\Controllers\API\Project\Order\ActionController as ProjectOrderActionController;
use App\Http\Controllers\API\Project\Order\StatusController as ProjectOrderStatusController;
use App\Http\Controllers\API\Project\Order\StepController as ProjectOrderStepController;

use App\Http\Controllers\API\Project\PermissionController as ProjectPermissionController;

use App\Http\Controllers\API\Project\PipedriveController as ProjectPipedriveController;
use App\Http\Controllers\API\Project\PipelineController as ProjectPipelineController;
use App\Http\Controllers\API\Project\Pipeline\LabelController as ProjectPipelineLabelController;

use App\Http\Controllers\API\Project\ProspectController as ProjectProspectController;
use App\Http\Controllers\API\Project\Prospect\AuthController as ProspectAuthController;
use App\Http\Controllers\API\Project\Prospect\AvailableEventController as ProspectAvailableEventController;
use App\Http\Controllers\API\Project\Prospect\DocumentController as ProspectDocumentController;
use App\Http\Controllers\API\Project\Prospect\EventController as ProspectEventController;
use App\Http\Controllers\API\Project\Prospect\FileController as ProspectFileController;
use App\Http\Controllers\API\Project\Prospect\FolderController as ProspectFolderController;
use App\Http\Controllers\API\Project\Prospect\GroupController as ProspectGroupController;
use App\Http\Controllers\API\Project\Prospect\InteractionController as ProspectInteractionController;
use App\Http\Controllers\API\Project\Prospect\LabelController as ProspectLabelController;
use App\Http\Controllers\API\Project\Prospect\LinkController as ProspectLinkController;
use App\Http\Controllers\API\Project\Prospect\LogController as ProspectLogController;
use App\Http\Controllers\API\Project\Prospect\MessageController as ProspectMessageController;
use App\Http\Controllers\API\Project\Prospect\MessageUserController as ProspectMessageUserController;
use App\Http\Controllers\API\Project\Prospect\MessageAttachmentController as ProspectMessageAttachmentController;
use App\Http\Controllers\API\Project\Prospect\OrderController as ProspectOrderController;
use App\Http\Controllers\API\Project\Prospect\Order\ActionController as ProspectOrderActionController;
use App\Http\Controllers\API\Project\Prospect\Order\DocumentController as ProspectOrderDocumentController;
use App\Http\Controllers\API\Project\Prospect\Order\InvoiceController as ProspectOrderInvoiceController;
use App\Http\Controllers\API\Project\Prospect\Order\ProductController as ProspectOrderProductController;
use App\Http\Controllers\API\Project\Prospect\Order\StepController as ProspectOrderStepController;
use App\Http\Controllers\API\Project\Prospect\PipelineController as ProspectPipelineController;
use App\Http\Controllers\API\Project\Prospect\QuestionnaireController as ProspectQuestionnaireController;
use App\Http\Controllers\API\Project\Prospect\QuestionnaireResponseController as ProspectQuestionnaireResponseController;
use App\Http\Controllers\API\Project\Prospect\RevisionController as ProspectRevisionController;
use App\Http\Controllers\API\Project\Prospect\SmsController as ProspectSmsController;
use App\Http\Controllers\API\Project\Prospect\ThreadController as ProspectThreadController;
use App\Http\Controllers\API\Project\Prospect\UserController as ProspectUserController;

use App\Http\Controllers\API\Project\Order\ProductController as ProjectProductController;
use App\Http\Controllers\API\Project\Order\ProductImageController as ProjectProductImageController;
use App\Http\Controllers\API\Project\QuestionnaireController as ProjectQuestionnaireController;
use App\Http\Controllers\API\Project\Questionnaire\SectionController as ProjectQuestionnaireSectionController;
use App\Http\Controllers\API\Project\Questionnaire\QuestionController as ProjectQuestionnaireQuestionController;
use App\Http\Controllers\API\Project\Questionnaire\OptionController as ProjectQuestionnaireOptionController;

use App\Http\Controllers\API\Project\RoleController as ProjectRoleController;
use App\Http\Controllers\API\Project\Role\CalendarController as ProjectRoleCalendarController;
use App\Http\Controllers\API\Project\Role\CategoryController as ProjectRoleCategoryController;
use App\Http\Controllers\API\Project\Role\DocumentController as ProjectRoleDocumentController;
use App\Http\Controllers\API\Project\Role\DuplicateController as ProjectRoleDuplicateController;
use App\Http\Controllers\API\Project\Role\FolderController as ProjectRoleFolderController;
use App\Http\Controllers\API\Project\Role\LabelController as ProjectRoleLabelController;
use App\Http\Controllers\API\Project\Role\MenuController as ProjectRoleMenuController;
use App\Http\Controllers\API\Project\Role\OrderStepController as ProjectRoleOrderStepController;
use App\Http\Controllers\API\Project\Role\PermissionController as ProjectRolePermissionController;
use App\Http\Controllers\API\Project\Role\ProductController as ProjectRoleProductController;
use App\Http\Controllers\API\Project\Role\QuestionnaireController as ProjectRoleQuestionnaireController;
use App\Http\Controllers\API\Project\Role\ThreadController as ProjectRoleThreadController;
use App\Http\Controllers\API\Project\Role\UserController as ProjectRoleUserController;

use App\Http\Controllers\API\Project\SettingController as ProjectSettingController;
use App\Http\Controllers\API\Project\SmsTemplateController as ProjectSmsTemplateController;
use App\Http\Controllers\API\Project\StatController as ProjectStatController;
use App\Http\Controllers\API\Project\Stat\MetricController as ProjectStatMetricController;
use App\Http\Controllers\API\Project\StatChartController as ProjectStatChartController;
use App\Http\Controllers\API\Project\ThreadController as ProjectThreadController;
use App\Http\Controllers\API\Project\TrashController as ProjectTrashController;

use App\Http\Controllers\API\Project\UserController as ProjectUserController;
use App\Http\Controllers\API\Project\User\ActionController as ProjectUserActionController;
use App\Http\Controllers\API\Project\User\CalendarController as ProjectUserCalendarController;
use App\Http\Controllers\API\Project\User\CategoryController as ProjectUserCategoryController;
use App\Http\Controllers\API\Project\User\DocumentController as ProjectUserDocumentController;
use App\Http\Controllers\API\Project\User\DuplicateController as ProjectUserDuplicateController;
use App\Http\Controllers\API\Project\User\EventController as ProjectUserEventController;
use App\Http\Controllers\API\Project\User\EventsDailyDirectionController as ProjectUserEventsDailyDirectionController;
use App\Http\Controllers\API\Project\User\FileController as ProjectUserFileController;
use App\Http\Controllers\API\Project\User\FolderController as ProjectUserFolderController;
use App\Http\Controllers\API\Project\User\GroupController as ProjectUserGroupController;
use App\Http\Controllers\API\Project\User\LabelController as ProjectUserLabelController;
use App\Http\Controllers\API\Project\User\MenuController as ProjectUserMenuController;
use App\Http\Controllers\API\Project\User\OrderStepController as ProjectUserOrderStepController;
use App\Http\Controllers\API\Project\User\PermissionController as ProjectUserPermissionController;
use App\Http\Controllers\API\Project\User\ProductController as ProjectUserProductController;
use App\Http\Controllers\API\Project\User\QuestionnaireController as ProjectUserQuestionnaireController;
use App\Http\Controllers\API\Project\User\RoleController as ProjectUserRoleController;
use App\Http\Controllers\API\Project\User\SettingController as ProjectUserSettingController;
use App\Http\Controllers\API\Project\User\ThreadController as ProjectUserThreadController;
use App\Http\Controllers\API\Project\User\TrackerController as ProjectUserTrackerController;
use App\Http\Controllers\API\Project\User\TrackerStatController as ProjectUserTrackerStatController;
use App\Http\Controllers\API\Project\User\UserController as ProjectUserUserController;
use App\Http\Controllers\API\Project\UserConnectionStatController as ProjectUserConnectionStatController;
use App\Http\Controllers\API\Project\UserImpersonateController as ProjectUserImpersonateController;
use App\Http\Controllers\API\Project\User\VehicleController as ProjectUserVehicleController;

use App\Http\Controllers\API\Project\VehicleController as ProjectVehicleController;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\StatChartController;
use App\Http\Controllers\API\UserImpersonateController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\User\BanController as UserBanController;
use App\Http\Controllers\API\User\EventsDailyDirectionController as UserEventsDailyDirectionController;
use App\Http\Controllers\API\User\ProjectController as UserProjectController;
use App\Http\Controllers\API\User\SessionController as UserSessionController;
use App\Http\Controllers\API\User\SettingController as UserSettingController;

use App\Http\Controllers\API\User\TrackerController as UserTrackerController;
use App\Http\Controllers\API\User\TrackerStatController as UserTrackerStatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

// Google Auth
Route::post('/google/auth/login', [App\Http\Controllers\API\Google\AuthController::class, 'login'])->name('google.auth.login');

// Two factors
Route::post('/google/authenticator/login', [GoogleAuthenticatorController::class, 'login']);

// User Permission
Route::get('permission', [PermissionController::class, 'index'])->name("permission");

// Webservice
Route::get('/webservice/{import}/prospect', [WebserviceController::class, 'prospect']);

Route::get('project/{project}/logo', [ProjectLogoController::class, 'show'])->name("project.logo");

Route::group([
    'middleware' => ['auth:sanctum']
], function () {

    // Authenticated user
    Route::get('/auth', function (Request $request) {
        $user = $request->user();

        if ($user) {
            $user->append([
                'two_factors_activated',
                'is_super_admin',
                'is_project_admin',
            ]);

            $manager = app('impersonate');

            $user->is_impersonating = $manager->isImpersonating();
            $user->impersonating_project = session('impersonate_project', null);
        }

        return $user;
    })->name('auth');

    // Find user by email
    Route::get('/user/email/{email}', function (Request $request, $email) {
        return User::where('email', $email)->first(['email', 'name', 'last_name']);
    })->name("user.email");

    // User
    Route::get('/user', [UserController::class, 'index'])->name("user.index");
    Route::post('/user', [UserController::class, 'store'])->name("user.store");
    Route::get('/user/{user}', [UserController::class, 'show'])->name("user.show");
    Route::put('/user/{user}', [UserController::class, 'update'])->name("user.update");

    // User tracker
    Route::get('/user/{user}/tracker', [UserTrackerController::class, "index"]);
    // User tracker stat
    Route::get('/user/{user}/tracker-stat', [UserTrackerStatController::class, "show"]);

    // Two factors
    Route::get('/google/authenticator/enable', [GoogleAuthenticatorController::class, 'enable'])->name('google.authenticator.enable');
    Route::post('/google/authenticator/activate', [GoogleAuthenticatorController::class, 'activate'])->name('google.authenticator.activate');
    Route::post('/google/authenticator/deactivate', [GoogleAuthenticatorController::class, 'deactivate'])->name('google.authenticator.deactivate');
    Route::get('/google/authenticator/check', [GoogleAuthenticatorController::class, 'check'])->name('google.authenticator.check');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Kavkom
    Route::post('/settings/kavkom/test', [KavkomController::class, 'test'])->name('settings.kavkom.test');
    Route::post('/settings/kavkom/call', [KavkomController::class, 'call'])->name('settings.kavkom.call');
    Route::get('/settings/kavkom/credentials', [KavkomController::class, 'credentials'])->name('settings.kavkom.credentials');
    Route::get('dashboard/projects', [DashboardController::class, 'projects'])->name('dashboard.projects');

    // Default field
    Route::get('default-field', [DefaultFieldController::class, 'index'])->name('default-field');

    // Event
    Route::get('event', [EventController::class, 'index'])->name('event.index');

    // Google Calendar
    Route::get('google/calendar', [GoogleCalendarController::class, 'index'])->name('google.calendar.index');
    Route::delete('google/calendar/{account}', [GoogleCalendarController::class, 'destroy'])->name('google.calendar.destroy');

    // Google Contact
    Route::get('google/contact', [GoogleContactController::class, 'index'])->name('google.contact.index');
    Route::delete('google/contact/{account}', [GoogleContactController::class, 'destroy'])->name('google.contact.destroy');

    // Project
    Route::match(['PUT', 'PATCH'], 'project/orders',  [ProjectController::class, 'orders']);
    Route::match(['PUT', 'PATCH'], 'project/bulk', [ProjectController::class, 'bulk']);
    Route::apiResource('project', ProjectController::class);

    Route::group([
        'prefix' => "project/{project}/",
        'as' => 'project.'
    ], function () {

        Route::post('api/pappers', [ProjectPappersController::class, 'index']);

        Route::get('calendar/{calendar}/user', [ProjectUserCalendarController::class, 'users']);
        Route::get('category/{category}/user', [ProjectUserCategoryController::class, 'users']);
        Route::get('document/{document}/user', [ProjectUserDocumentController::class, 'users']);
        Route::get('folder/{folder}/user', [ProjectUserFolderController::class, 'users']);
        Route::get('group/{group}/user', [ProjectUserGroupController::class, 'users']);
        Route::get('label/{label}/user', [ProjectUserLabelController::class, 'users']);
        Route::get('menu/{menu}/user', [ProjectUserMenuController::class, 'users']);
        Route::get('order-step/{orderStep}/user', [ProjectUserOrderStepController::class, 'users']);
        Route::get('product/{product}/user', [ProjectUserProductController::class, 'users']);
        Route::get('questionnaire/{questionnaire}/user', [ProjectUserQuestionnaireController::class, 'users']);
        Route::get('role/{role}/user', [ProjectUserRoleController::class, 'users']);
        Route::get('thread/{thread}/user', [ProjectUserThreadController::class, 'users']);
        Route::get('assignable-user/{user}/user', [ProjectUserUserController::class, 'users']);

        // Calendar
        Route::apiResource('calendar', ProjectCalendarController::class);
        // Category
        Route::match(['PUT', 'PATCH'], 'category/orders',  [ProjectCategoryController::class, 'orders']);
        Route::match(['PUT', 'PATCH'], 'category/{category}/combine/{category2}', [ProjectCategoryController::class, 'combine']);
        Route::match(['PUT', 'PATCH'], 'category/{category}/field', [ProjectCategoryController::class, 'field']);
        Route::apiResource('category', ProjectCategoryController::class);
        // Category label
        Route::match(['PUT', 'PATCH'], 'category/{category}/label/orders',  [ProjectLabelController::class, 'orders']);
        Route::match(['PUT', 'PATCH'], 'category/{category}/label/bulk',  [ProjectLabelController::class, 'bulk']);
        Route::apiResource('category.label', ProjectLabelController::class);
        Route::match(['PUT', 'PATCH'], 'prospect/bulk', [ProjectProspectController::class, 'bulk']);
        // Commission
        Route::get('commission', [ProjectCommissionController::class, 'index'])->name('commission');
        Route::match(['PUT', 'PATCH'], 'commission/bulk', [ProjectCommissionController::class, 'bulk'])->name('commission.bulk');
        Route::get('user/{user}/product/{product}/action/{action}/commission', [ProjectCommissionController::class, 'index'])->name('commission.user-product-action.show');
        Route::match(['PUT', 'PATCH'], 'user/{user}/product/{product}/action/{action}/commission', [ProjectCommissionController::class, 'update'])->name('commission.user-product-action.update');
        Route::delete('user/{user}/product/{product}/action/{action}/commission', [ProjectCommissionController::class, 'destroy'])->name('commission.user-product-action.destroy');

        // Campaign
        Route::get('campaign/{campaign}/check-rules', [ProjectCampaignController::class, 'checkRules'])->name('campaign.check-rules');
        Route::apiResource('campaign', ProjectCampaignController::class);
        Route::apiResource('campaign-action', ProjectCampaignActionController::class);
        Route::apiResource('campaign.action', ProjectCampaignCampaignActionController::class)->only('index', 'update', 'destroy');
        Route::apiResource('campaign.operator', ProjectCampaignCampaignOperatorController::class)->only('index', 'update', 'destroy');
        Route::apiResource('campaign.rule', ProjectCampaignCampaignRuleController::class)->only('index', 'update', 'destroy');
        Route::apiResource('campaign-operator', ProjectCampaignOperatorController::class);

        Route::get('campaign-operator/{parent}/operator', [ProjectCampaignOperatorOperatorController::class, 'index'])->name('campaign-operator.child.show');
        Route::match(['PUT', 'PATCH'], 'campaign-operator/{parent}/operator/{child}', [ProjectCampaignOperatorOperatorController::class, 'update'])->name('campaign-operator.child.update');
        Route::delete('campaign-operator/{parent}/operator/{child}', [ProjectCampaignOperatorOperatorController::class, 'destroy'])->name('campaign-operator.child.destroy');

        Route::get('campaign/{campaign}/order', [ProjectCampaignOrderController::class, 'index'])->name('campaign.order');
        Route::get('campaign/{campaign}/order/matching', [ProjectCampaignOrderController::class, 'matching'])->name('campaign.order.matching');

        Route::get('campaign/{campaign}/prospect', [ProjectCampaignProspectController::class, 'index']);
        Route::get('campaign/{campaign}/prospect/matching', [ProjectCampaignProspectController::class, 'matching']);

        Route::get('campaign-operator/{operator}/rule', [ProjectCampaignOperatorRuleController::class, 'index']);
        Route::match(['PUT', 'PATCH'], 'campaign-operator/{operator}/rule/{rule}', [ProjectCampaignOperatorRuleController::class, 'update']);
        Route::delete('campaign-operator/{operator}/rule/{rule}', [ProjectCampaignOperatorRuleController::class, 'destroy']);

        Route::apiResource('campaign-rule', ProjectCampaignRuleController::class);

        // Default menu
        Route::get('default-menu', [ProjectDefaultMenuController::class, 'index']);

        // Document
        Route::get('document-template', [ProjectDocumentTemplateController::class, 'index'])->name("document-template.index");
        Route::post('document-template/{template}', [ProjectDocumentTemplateController::class, 'store'])->name("document-template.store");
        Route::apiResource('document', ProjectDocumentController::class);
        Route::get('document/{document}/thumbnail', [ProjectDocumentController::class, 'thumbnail'])->name("document.thumbnail")->withoutMiddleware("throttle:api");
        // Document Font
        Route::apiResource('document.font', ProjectDocumentFontController::class)->only('index', 'store', 'show', 'update', 'destroy');
        // Document File
        Route::apiResource('document.file', ProjectDocumentFileController::class)->only('index', 'store', 'show', 'destroy');
        Route::post('document/{document}/file/{file}', [ProjectDocumentFileController::class, 'update'])->name("document.file.update");
        Route::get('document/{document}/file/{file}/page/{page}/thumbnail', [ProjectDocumentFileController::class, 'thumbnail'])->withoutMiddleware("throttle:api");
        // Document Page
        Route::apiResource('document.page', ProjectDocumentPageController::class);
        Route::get('document/{document}/page/{page}/thumbnail', [ProjectDocumentPageController::class, 'thumbnail'])->withoutMiddleware("throttle:api");
        // Document Page field
        Route::apiResource('document.field', ProjectDocumentFieldController::class);

        // Duplicate
        Route::get('duplicate/find', [ProjectDuplicateController::class, 'find']);
        Route::get('duplicate/show', [ProjectDuplicateController::class, 'show']);
        Route::delete('duplicate', [ProjectDuplicateController::class, 'destroy']);

        // Event
        Route::apiResource('event', ProjectEventController::class)->withoutMiddleware("throttle:api");
        // Event description Template
        Route::apiResource('event-description-template', ProjectEventDescriptionTemplateController::class);
        // Field
        Route::match(['PUT', 'PATCH'], 'field/orders',  [ProjectFieldController::class, 'orders']);
        Route::match(['PUT', 'PATCH'], 'field/{field}/category', [ProjectFieldController::class, 'category']);
        Route::apiResource('field', ProjectFieldController::class);
        // Folder
        Route::apiResource('folder', ProjectFolderController::class);
        // Group
        Route::apiResource('group', ProjectGroupController::class);

        // Google Drive
        Route::get('google/drive', [ProjectGoogleDriveController::class, 'index']);
        Route::delete('google/drive/{account}', [ProjectGoogleDriveController::class, 'destroy']);

        // Import
        Route::apiResource('import', ProjectImportController::class);
        Route::get('/import/{import}/download', [ProjectImportController::class, 'download'])->name("import.download");
        Route::get('/import/{import}/duplicate/find', [ProjectImportDuplicateController::class, 'find']);
        Route::get('/import/{import}/duplicate/show', [ProjectImportDuplicateController::class, 'show']);
        Route::delete('/import/{import}/duplicate', [ProjectImportDuplicateController::class, 'destroy']);
        // Import Relation Group
        Route::apiResource('import.group', ProjectImportGroupController::class)->only('index', 'update', 'destroy');
        // Import Relation Label
        Route::apiResource('import.label', ProjectImportLabelController::class)->only('index', 'update', 'destroy');
        // Import Relation User
        Route::apiResource('import.user', ProjectImportUserController::class)->only('index', 'update', 'destroy');
        // Import Mapping
        Route::get('/import/{import}/mapping', [ProjectImportMappingController::class, 'index']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/field/{slug}', [ProjectImportMappingController::class, 'field']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/meta/{slug}', [ProjectImportMappingController::class, 'meta']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/category/{category}', [ProjectImportMappingController::class, 'category']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/thread/{thread}', [ProjectImportMappingController::class, 'thread']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/event', [ProjectImportMappingController::class, 'event']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/order', [ProjectImportMappingController::class, 'order']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/sms', [ProjectImportMappingController::class, 'sms']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/interaction', [ProjectImportMappingController::class, 'interaction']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/link', [ProjectImportMappingController::class, 'link']);
        Route::match(['PUT', 'PATCH'], '/import/{import}/mapping/{index}/user', [ProjectImportMappingController::class, 'user']);
        Route::delete('/import/{import}/mapping/{index}', [ProjectImportMappingController::class, 'destroy']);

        // Logo
        Route::post('logo', [ProjectLogoController::class, 'store']);

        // Menu
        Route::apiResource('menu', ProjectMenuController::class);

        // Menu icon
        Route::get('menu-icon/{menu}', [ProjectMenuIconController::class, 'show']);
        Route::post('menu-icon/{menu}', [ProjectMenuIconController::class, 'store']);
        Route::delete('menu-icon/{menu}', [ProjectMenuIconController::class, 'destroy']);

        // Message Template
        Route::apiResource('message-template', ProjectMessageTemplateController::class);

        // OCR
        Route::get('ocr/{ocr}', [ProjectOcrController::class, 'show']);
        Route::post('ocr', [ProjectOcrController::class, 'store']);

        // Order
        Route::get('order', [ProjectOrderController::class, 'index'])->name("order.index");

        // Order Action
        Route::apiResource('order-action', ProjectOrderActionController::class);
        // Order Status
        Route::apiResource('order-status', ProjectOrderStatusController::class);
        // Order Step
        Route::apiResource('order-step', ProjectOrderStepController::class);

        // User Permission
        Route::get('permission', [ProjectPermissionController::class, 'index'])->name("permission");

        // Pipedrive
        Route::apiResource('pipedrive', ProjectPipedriveController::class)->only('index', 'store', 'delete');
        Route::get('pipedrive/{pipedrive}/person', [ProjectPipedriveController::class, 'person']);
        Route::post('pipedrive/{pipedrive}/webhook', [ProjectPipedriveController::class, 'webhook']);
        Route::post('pipedrive/{pipedrive}/import', [ProjectPipedriveController::class, 'import']);
        // Pipeline
        Route::get('pipeline/{pipeline}/count',  [ProjectPipelineController::class, 'count']);
        Route::apiResource('pipeline', ProjectPipelineController::class);
        Route::match(['PUT', 'PATCH'], 'pipeline/{pipeline}/label/orders', [ProjectPipelineLabelController::class, 'orders']);
        Route::apiResource('pipeline/{pipeline}/label', ProjectPipelineLabelController::class)->only('index', 'update', 'destroy');
        Route::match(['PUT', 'PATCH'], 'pipeline/label/bulk', [ProjectPipelineLabelController::class, 'bulk']);

        // Product
        Route::apiResource('product', ProjectProductController::class);
        // Product image
        Route::apiResource('product.image', ProjectProductImageController::class);
        Route::get('product/{product}/image/{image}/thumbnail', [ProjectProductImageController::class, 'thumbnail'])->name("product.image.thumbnail");

        // Prospect Export
        Route::apiResource('export', ProjectExportController::class)->only('index', 'create', 'show', 'destroy');

        // Prospect bulk
        // Prospect
        Route::match(['PUT', 'PATCH'], 'prospect/bulk', [ProjectProspectController::class, 'bulk']);
        // Group
        Route::match(['PUT', 'PATCH'], 'prospect/group/bulk', [ProspectGroupController::class, 'bulk']);
        // Label
        Route::match(['PUT', 'PATCH'], 'prospect/label/bulk', [ProspectLabelController::class, 'bulk']);
        // Message
        Route::post('thread/{thread}/message/bulk', [ProspectMessageController::class, 'bulk']);
        // SMS
        Route::post('sms/bulk', [ProspectSmsController::class, 'bulk']);
        // User
        Route::match(['PUT', 'PATCH'], 'prospect/user/bulk', [ProspectUserController::class, 'bulk']);

        // Prospect
        Route::get('prospect/count', [ProjectProspectController::class, 'count']);
        Route::apiResource('prospect', ProjectProspectController::class);
        Route::post('prospect/{prospect}/duplicate', [ProjectProspectController::class, 'duplicate']);

        // Prospect
        Route::group([
            'prefix' => "prospect/{prospect}/",
            'as' => 'prospect.'
        ], function () {

            // Auth
            Route::get('auth', [ProspectAuthController::class, 'index']);

            // Available Event
            Route::apiResource('available-event', ProspectAvailableEventController::class);

            // Document
            Route::get('document/{document}', [ProspectDocumentController::class, 'show']);
            // Event
            Route::apiResource('event', ProspectEventController::class);
            // File
            Route::apiResource('folder.file', ProspectFileController::class)->only('index', 'store', 'show', 'destroy');
            Route::get('folder/{folder}/file/{file}/thumbnail', [ProspectFileController::class, 'thumbnail'])->name("folder.file.thumbnail");
            Route::get('folder/{folder}/file/{file}/user', [ProspectFileController::class, 'users']);
            Route::match(['PUT', 'PATCH'], 'folder/{folder}/user/file/bulk', [ProspectFileController::class, 'bulk']);
            // Folder
            Route::get('folder', [ProspectFolderController::class, 'index']);
            // Group
            Route::apiResource('group', ProspectGroupController::class)->only('index', 'update', 'destroy');
            // Interaction
            Route::get('interaction/{interaction}/audio', [ProspectInteractionController::class, 'audio'])->name("interaction.audio");
            Route::apiResource('interaction', ProspectInteractionController::class)->only('index', 'store', 'show', 'update', 'destroy');
            // Label
            Route::apiResource('label', ProspectLabelController::class)->only('index', 'update', 'destroy');
            // Link
            Route::apiResource('link', ProspectLinkController::class);
            // Log
            Route::get('log', [ProspectLogController::class, 'index']);
            // Message
            Route::apiResource('thread.message', ProspectMessageController::class);
            // Message User
            Route::match(['PUT', 'PATCH'], 'thread/{thread}/message/{message}/user/{user}', [ProspectMessageUserController::class, 'update']);
            Route::delete('thread/{thread}/message/{message}/user/{user}', [ProspectMessageUserController::class, 'destroy']);
            // Message Attachment
            Route::get('thread/{thread}/message/{message}/attachment/{attachment}', [ProspectMessageAttachmentController::class, 'show'])->name("thread.message.attachment.show");
            Route::get('thread/{thread}/message/{message}/attachment/{attachment}/thumbnail', [ProspectMessageAttachmentController::class, 'thumbnail'])->name("thread.message.attachment.thumbnail");
            Route::delete('thread/{thread}/message/{message}/attachment/{attachment}', [ProspectMessageAttachmentController::class, 'destroy']);
            // Order
            Route::apiResource('order', ProspectOrderController::class);

            // Order Action
            Route::apiResource('order.action', ProspectOrderActionController::class)->only('index', 'update', 'destroy');
            // Order Document
            Route::get('order/{order}/document/{document}', [ProspectOrderDocumentController::class, 'show']);
            // Route::get('order/{order}/document/{document}/generate', [ProspectOrderDocumentController::class, 'generate']);
            Route::get('order/{order}/invoice/{document}', [ProspectOrderInvoiceController::class, 'show']);
            Route::get('order/{order}/invoice/{document}/generate', [ProspectOrderInvoiceController::class, 'generate']);
            // Order Label
            Route::apiResource('order.label', ProspectOrderActionController::class)->only('index', 'update', 'destroy');
            // Order Product
            Route::apiResource('order.product', ProspectOrderProductController::class)->only('index', 'update', 'destroy');
            // Order Step
            Route::apiResource('order.step', ProspectOrderStepController::class)->only('index', 'update', 'destroy');

            // Pipeline
            Route::apiResource('pipeline/{pipeline}/label', ProspectPipelineController::class)->only('index', 'update')
                ->name('index', 'pipeline.label.index')
                ->name('update', 'pipeline.label.update');

            // Questionnaire
            Route::apiResource('questionnaire', ProspectQuestionnaireController::class)->only('index', 'show', 'update', 'destroy');
            // Questionnaire response
            Route::get('question/{question}', [ProspectQuestionnaireResponseController::class, 'show'])->name("question.show");
            Route::post('question/{question}', [ProspectQuestionnaireResponseController::class, 'store']);
            Route::delete('question/{question}', [ProspectQuestionnaireResponseController::class, 'destroy']);
            // Revision
            Route::get('revision', [ProspectRevisionController::class, 'index']);
            // Sms
            Route::apiResource('sms', ProspectSmsController::class)->only('index', 'store', 'destroy');
            // Thread
            Route::get('thread', [ProspectThreadController::class, 'index']);
            // User
            Route::apiResource('user', ProspectUserController::class)->only('index', 'update', 'destroy');

        });

        // Questionnaire
        Route::apiResource('questionnaire', ProjectQuestionnaireController::class);
        Route::apiResource('questionnaire.section', ProjectQuestionnaireSectionController::class);
        Route::apiResource('questionnaire.section.question', ProjectQuestionnaireQuestionController::class);
        Route::apiResource('questionnaire.section.question.option', ProjectQuestionnaireOptionController::class);

        // Setting
        Route::get('/setting/{setting}/check', [ProjectSettingController::class, 'check']);
        Route::get('/setting/{setting}', [ProjectSettingController::class, 'show']);
        Route::match(['PUT', 'PATCH'], '/setting/{setting}', [ProjectSettingController::class, 'update']);
        Route::delete('/setting/{setting}', [ProjectSettingController::class, 'destroy']);

        // Sms Template
        Route::apiResource('sms-template', ProjectSmsTemplateController::class);

        Route::match(['PUT', 'PATCH'], 'role/calendar/bulk', [ProjectRoleCalendarController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/category/bulk', [ProjectRoleCategoryController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/document/bulk', [ProjectRoleDocumentController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/folder/bulk', [ProjectRoleFolderController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/label/bulk', [ProjectRoleLabelController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/menu/bulk', [ProjectRoleMenuController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/order-step/bulk', [ProjectRoleOrderStepController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/product/bulk', [ProjectRoleProductController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/questionnaire/bulk', [ProjectRoleQuestionnaireController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/thread/bulk', [ProjectRoleThreadController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'role/assignable-user/bulk', [ProjectRoleUserController::class, 'bulk']);

        Route::get('calendar/{calendar}/role', [ProjectRoleCalendarController::class, 'roles']);
        Route::get('category/{category}/role', [ProjectRoleCategoryController::class, 'roles']);
        Route::get('document/{document}/role', [ProjectRoleDocumentController::class, 'roles']);
        Route::get('folder/{folder}/role', [ProjectRoleFolderController::class, 'roles']);
        Route::get('label/{label}/role', [ProjectRoleLabelController::class, 'roles']);
        Route::get('menu/{menu}/role', [ProjectRoleMenuController::class, 'roles']);
        Route::get('order-step/{orderStep}/role', [ProjectRoleOrderStepController::class, 'roles']);
        Route::get('product/{product}/role', [ProjectRoleProductController::class, 'roles']);
        Route::get('questionnaire/{questionnaire}/role', [ProjectRoleQuestionnaireController::class, 'roles']);
        Route::get('thread/{thread}/role', [ProjectRoleThreadController::class, 'roles']);
        Route::get('assignable-user/{role}/role', [ProjectRoleUserController::class, 'roles']);

        // Role
        Route::apiResource('role', ProjectRoleController::class);

        Route::group([
            'prefix' => "role/{role}/",
            'as' => 'role.'
        ], function () {

            // Role calendar
            Route::apiResource('calendar', ProjectRoleCalendarController::class)->only('index', 'update', 'destroy');
            // Role category
            Route::apiResource('category', ProjectRoleCategoryController::class)->only('index', 'update', 'destroy');
            // Role document
            Route::apiResource('document', ProjectRoleDocumentController::class)->only('index', 'update', 'destroy');
            // Role duplicate profile
            Route::match(['PUT', 'PATCH'], 'duplicate', [ProjectRoleDuplicateController::class, "duplicate"]);
            // Role folder
            Route::apiResource('folder', ProjectRoleFolderController::class)->only('index', 'update', 'destroy');
            // Role label
            Route::apiResource('label', ProjectRoleLabelController::class)->only('index', 'update', 'destroy');
            // Role menu
            Route::apiResource('menu', ProjectRoleMenuController::class)->only('index', 'update', 'destroy');
            // Role order step
            Route::apiResource('order-step', ProjectRoleOrderStepController::class)->only('index', 'update', 'destroy');
            // Role permission
            Route::apiResource('permission', ProjectRolePermissionController::class)->only('index', 'update', 'destroy');
            // Role product
            Route::apiResource('product', ProjectRoleProductController::class)->only('index', 'update', 'destroy');
            // Role questionnaire
            Route::apiResource('questionnaire', ProjectRoleQuestionnaireController::class)->only('index', 'update', 'destroy');
            // Role thread
            Route::apiResource('thread', ProjectRoleThreadController::class)->only('index', 'update', 'destroy');
            // Role Role
            Route::apiResource('assignable-user', ProjectRoleUserController::class)->only('index', 'update', 'destroy');

        });

        // Stat
        Route::get('stat', [ProjectStatController::class, 'index']);
        Route::post('stat', [ProjectStatController::class, 'store']);
        Route::delete('stat/{index}', [ProjectStatController::class, 'destroy']);
        Route::get('stat/country/{key}', [ProjectStatController::class, 'country']);
        Route::get('stat/prospect-relation/{key}', [ProjectStatController::class, 'prospectRelation']);
        Route::get('stat/{key}', [ProjectStatController::class, 'show']);
        Route::get('stat/chart/{index}', [ProjectStatController::class, 'chart']);

        // Stat Metric
        Route::apiResource('metric', ProjectStatMetricController::class);

        // Stat Chart
        Route::apiResource('stat-chart', ProjectStatChartController::class)->only('show', 'store', 'update', 'destroy');

        // Thread
        Route::apiResource('thread', ProjectThreadController::class);

        // Trash
        Route::get('trash/prospect', [ProjectTrashController::class, 'prospect']);
        Route::get('trash/category', [ProjectTrashController::class, 'category']);
        Route::get('trash/label', [ProjectTrashController::class, 'label']);
        Route::get('trash/thread', [ProjectTrashController::class, 'thread']);
        Route::get('trash/message', [ProjectTrashController::class, 'message']);
        Route::get('trash/folder', [ProjectTrashController::class, 'folder']);
        Route::get('trash/file', [ProjectTrashController::class, 'file']);
        Route::get('trash/product', [ProjectTrashController::class, 'product']);
        Route::get('trash/order-action', [ProjectTrashController::class, 'orderAction']);
        Route::get('trash/order-status', [ProjectTrashController::class, 'orderStatus']);
        Route::get('trash/order-step', [ProjectTrashController::class, 'orderStep']);
        Route::get('trash/order', [ProjectTrashController::class, 'order']);
        Route::get('trash/calendar', [ProjectTrashController::class, 'calendar']);
        Route::get('trash/event', [ProjectTrashController::class, 'event']);
        Route::match(['PUT', 'PATCH'], 'trash/bulk', [ProjectTrashController::class, 'bulk']);

        Route::match(['PUT', 'PATCH'], 'user/calendar/bulk', [ProjectUserCalendarController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/category/bulk', [ProjectUserCategoryController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/document/bulk', [ProjectUserDocumentController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/folder/bulk', [ProjectUserFolderController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/group/bulk', [ProjectUserGroupController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/label/bulk', [ProjectUserLabelController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/menu/bulk', [ProjectUserMenuController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/order-step/bulk', [ProjectUserOrderStepController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/questionnaire/bulk', [ProjectUserQuestionnaireController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/product/bulk', [ProjectUserProductController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/role/bulk', [ProjectUserRoleController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/thread/bulk', [ProjectUserThreadController::class, 'bulk']);
        Route::match(['PUT', 'PATCH'], 'user/assignable-user/bulk', [ProjectUserUserController::class, 'bulk']);

        // User events daily direction
        Route::get('user/{user}/events-daily-direction/{date}', [ProjectUserEventsDailyDirectionController::class, 'index'])->withoutMiddleware("throttle:api");

        Route::match(['PUT', 'PATCH'], 'user/bulk', [ProjectUserController::class, 'bulk']);

        // Connection stat per month
        Route::get('user/connection-stat', [ProjectUserConnectionStatController::class, "index"])->name('user.connection-stat.index');
        Route::get('user/connection-stat/{date}', [ProjectUserConnectionStatController::class, "show"])->name('user.connection-stat.show');

        Route::post('user/impersonate/leave', [ProjectUserImpersonateController::class, "leaveImpersonation"])->name('user.impersonate.leave');

        // User
        Route::apiResource('user', ProjectUserController::class);

        Route::group([
            'prefix' => "user/{user}/",
            'as' => 'user.'
        ], function () {
            // Impersonate
            Route::post('impersonate', [ProjectUserImpersonateController::class, "impersonate"])->name('impersonate');

            // User action
            Route::get('action', [ProjectUserActionController::class, "index"]);
            // User calendar
            Route::apiResource('calendar', ProjectUserCalendarController::class)->only('index', 'update', 'destroy');
            // User category
            Route::apiResource('category', ProjectUserCategoryController::class)->only('index', 'update', 'destroy');
            // User document
            Route::apiResource('document', ProjectUserDocumentController::class)->only('index', 'update', 'destroy');
            // User duplicate profile
            Route::match(['PUT', 'PATCH'], 'duplicate', [ProjectUserDuplicateController::class, "duplicate"]);
            // User event
            Route::apiResource('event', ProjectUserEventController::class)->only('update', 'destroy');
            // User File
            Route::apiResource('folder.file', ProjectUserFileController::class)->only('index', 'store', 'show', 'destroy');
            Route::get('folder/{folder}/file/{file}/thumbnail', [ProjectUserFileController::class, 'thumbnail'])->name("folder.file.thumbnail");
            // User folder
            Route::apiResource('folder', ProjectUserFolderController::class)->only('index', 'update', 'destroy');
            // User group
            Route::apiResource('group', ProjectUserGroupController::class)->only('index', 'update', 'destroy');
            // User label
            Route::apiResource('label', ProjectUserLabelController::class)->only('index', 'update', 'destroy');
            // User menu
            Route::apiResource('menu', ProjectUserMenuController::class)->only('index', 'update', 'destroy');
            // User waiting message
            Route::apiResource('message', ProjectUserLabelController::class)->only('update', 'destroy');
            // User order step
            Route::apiResource('order-step', ProjectUserOrderStepController::class)->only('index', 'update', 'destroy');
            // User permission
            Route::apiResource('permission', ProjectUserPermissionController::class)->only('index', 'update', 'destroy');
            // User questionnaire
            Route::apiResource('product', ProjectUserProductController::class)->only('index', 'update', 'destroy');
            // User questionnaire
            Route::apiResource('questionnaire', ProjectUserQuestionnaireController::class)->only('index', 'update', 'destroy');
            // User role
            Route::apiResource('role', ProjectUserRoleController::class)->only('index', 'update', 'destroy');
            // User thread
            Route::apiResource('thread', ProjectUserThreadController::class)->only('index', 'update', 'destroy');
            // User tracker
            Route::get('tracker', [ProjectUserTrackerController::class, "index"]);
            // User tracker stat
            Route::get('tracker-stat', [ProjectUserTrackerStatController::class, "show"]);
            // User Setting
            Route::apiResource('setting', ProjectUserSettingController::class)->only('show', 'update', 'destroy');
            // User User
            Route::apiResource('assignable-user', ProjectUserUserController::class)->only('index', 'update', 'destroy');
            // User Vehicle
            Route::apiResource('vehicle', ProjectUserVehicleController::class);

        });

        Route::apiResource('vehicle', ProjectVehicleController::class);
        Route::get('vehicle/{vehicle}/positions', [ProjectVehicleController::class, "positions"]);
    });

    // Stat Chart
    Route::apiResource('stat-chart', StatChartController::class)->only('index', 'store');
    Route::match(['PUT', 'PATCH'], 'stat-chart/orders',  [StatChartController::class, 'orders']);

    Route::post('user/impersonate/leave', [UserImpersonateController::class, "leaveImpersonation"])->name('user.impersonate.leave');

    Route::group([
        'prefix' => "user/{user}/",
        'as' => 'user.'
    ], function () {

        // Ban
        Route::match(['PUT', 'PATCH'], '/ban', [UserBanController::class, "update"])->name('ban.store');
        Route::delete('/ban', [UserBanController::class, "destroy"])->name('ban.delete');

        // Events daily direction
        Route::get('events-daily-direction/{date}', [UserEventsDailyDirectionController::class, 'index'])->withoutMiddleware("throttle:api");

        // Impersonate
        Route::post('impersonate', [UserImpersonateController::class, "impersonate"])->name('impersonate');

        // User project
        Route::apiResource('project', UserProjectController::class)->only('index', 'update', 'destroy');

        // User Setting
        Route::apiResource('setting', UserSettingController::class)->only('show', 'update', 'delete');

        // User Sessions
        Route::get('session', [UserSessionController::class, "index"])->name('session.index');
        Route::get('session/geoip', [UserSessionController::class, "geoip"])->name('session.geoip');
        Route::post('session/{session}/ban', [UserSessionController::class, "ban"])->name('session.ban.store');
        Route::delete('session/{session}/ban', [UserSessionController::class, "cancelBan"])->name('session.ban.delete');
        Route::match(['PUT', 'PATCH'], 'session/{session}/ban', [UserSessionController::class, "confirmBan"])->name('session.ban.confirm');
    });

});
