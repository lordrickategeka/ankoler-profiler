<?php

use App\Http\Controllers\PersonSearchController;
use App\Livewire\Dashboard\DashboardComponent;
use App\Livewire\Person\PersonList;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RelationshipController;
use App\Http\Controllers\SMSWebhookController;
use App\Http\Controllers\AfricasTalkingCallbackController;
use App\Livewire\Person\Notifications as PersonNotificationsLivewire;


Route::get('/', function () {
    return view('auth.login');
});
Route::get('/test-at', function () {
    function testApiConnection($AT, $from, $to)
    {
        try {
            $voice = $AT->voice();
            $results = $voice->call(['from' => $from, 'to' => $to]);
            return ['success' => true, 'message' => 'API key is active!'];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'authentication') !== false) {
                return ['success' => false, 'message' => 'API key still activating...'];
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    ])->group(function () {

    Route::get('/dashboard', DashboardComponent::class)->name('dashboard');

    // Organizations routes
    Route::get('/organizations', App\Livewire\Organizations\Index::class)
        ->name('organizations.index')
        ->middleware('can:view-organisations');

    Route::get('/organizations/create', App\Livewire\Organizations\Create::class)
        ->name('organizations.create')
        ->middleware('can:create-organisations');

    Route::get('/organizations/{id}', App\Livewire\Organizations\Show::class)
        ->name('organizations.show')
        ->middleware('can:view-organisations');

    // Person routes
    Route::get('/persons', PersonList::class)->name('persons');
    Route::get('/persons/create', App\Livewire\Person\CreatePerson::class)->name('persons.create');
    Route::get('/persons/import', App\Livewire\Person\ImportPersons::class)
        ->name('persons.import')
        ->middleware('can:import-org-persons');
    Route::get('/persons/export', App\Livewire\Person\ExportPersons::class)
        ->name('persons.export');

    // Person profile view
    Route::get('/persons/profile-current', App\Livewire\Person\ProfileView::class)
        ->name('persons.profile-current');

    // Organisation Units for current user
    Route::get('/my-organisation-units', App\Livewire\Person\OrganisationUnitsList::class)
        ->name('person.organisation-units');


    Route::get('/person/notifications', PersonNotificationsLivewire::class)->name('person.notifications');

    // Organization Units - User listing and application
    Route::get('/organization-units', App\Livewire\Organizations\ListOrganizationUnits::class)
        ->name('organization-units.index')
        ->middleware('can:view-organisation-units');

    Route::get('/organization-units/create', App\Livewire\Organizations\CreateOrganizationUnit::class)
        ->name('organization-units.create')
        ->middleware('can:create-units');

    // Organization Units - Admin review of applications
    Route::get('/organization-units/applications', App\Livewire\Organizations\ReviewUnitApplications::class)
        ->name('organization-units.applications')
        ->middleware('can:review-organization-units');


// Route::resource('persons', PersonSearchController::class);
Route::get('persons/search', [PersonSearchController::class, 'index2'])->name('person-search');
Route::get('persons/search/api', [PersonSearchController::class, 'search'])->name('persons.search.api');
Route::get('persons/search/suggestions', [PersonSearchController::class, 'suggestions'])->name('persons.search.suggestions');
Route::post('persons/search/export', [PersonSearchController::class, 'export'])->name('persons.search.export');

    // Admin routes - Role and Permission Management
    Route::prefix('admin')->name('admin.')->middleware('can:manage-roles')->group(function () {
        Route::get('/permissions', App\Livewire\Admin\PermissionManager::class)
            ->name('permissions.index');

        Route::get('/roles', App\Livewire\Admin\RoleManager::class)
            ->name('roles.index');

        Route::get('/role-types', App\Livewire\Admin\RoleTypeManager::class)
            ->name('role-types.index');

        Route::get('/users', App\Livewire\Admin\UserManager::class)
            ->name('users.index');
    });

    // Organization Units routes
    // Route::get('/organization-units', App\Livewire\Organizations\OrganisationUnitsComponent::class)
    //     ->name('organization-units.index')
    //     ->middleware('can:view-units');

    // Communication routes
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/', [App\Http\Controllers\CommunicationController::class, 'index'])
            ->name('index');

        Route::get('/send', [App\Http\Controllers\CommunicationController::class, 'sendMessage'])
            ->name('send')
            ->middleware('can:send-communications');

        Route::get('/filter-profiles', App\Livewire\Communication\FilterProfiles::class)
            ->name('filter-profiles')
            ->middleware('can:send-communications');

        Route::get('/history', [App\Http\Controllers\CommunicationController::class, 'history'])
            ->name('history')
            ->middleware('can:view-communications');

        Route::get('/settings', [App\Http\Controllers\CommunicationController::class, 'settings'])
            ->name('settings')
            ->middleware('can:manage-communications');
    });

});

Route::middleware(['auth'])->prefix('relationships')->name('relationships.')->group(function () {
    // Dashboard and overview
    Route::get('/', [RelationshipController::class, 'index'])->name('index');
    Route::get('/network-analysis', [RelationshipController::class, 'getNetworkAnalysis'])->name('network-analysis');

    // Personal relationships
    Route::get('/personal', [RelationshipController::class, 'personalRelationships'])->name('personal');
    Route::post('/personal', [RelationshipController::class, 'createManualRelationship'])->name('personal.create');
    Route::post('/personal/{relationship}/verify', [RelationshipController::class, 'verifyPersonalRelationship'])->name('personal.verify');
    Route::post('/personal/{relationship}/reject', [RelationshipController::class, 'rejectPersonalRelationship'])->name('personal.reject');

    // Cross-organizational relationships
    Route::get('/cross-org', [RelationshipController::class, 'crossOrgRelationships'])->name('cross-org');
    Route::post('/cross-org/{relationship}/verify', [RelationshipController::class, 'verifyCrossOrgRelationship'])->name('cross-org.verify');

    // Person network view
    Route::get('/person/{person}/network', [RelationshipController::class, 'personNetwork'])->name('person.network');

    // Discovery and management
    Route::post('/discover', [RelationshipController::class, 'runDiscovery'])->name('discover');
    Route::get('/export', [RelationshipController::class, 'exportRelationships'])->name('export');
});

// API routes for AJAX calls
Route::middleware(['auth:sanctum'])->prefix('api/relationships')->name('api.relationships.')->group(function () {
    Route::get('/stats', [RelationshipController::class, 'getRelationshipStats'])->name('stats');
    Route::get('/pending', [RelationshipController::class, 'getPendingVerifications'])->name('pending');
    Route::get('/network-data/{person}', [RelationshipController::class, 'getPersonNetworkData'])->name('network-data');
});

Route::post('/webhooks/africastalking/delivery-reports', [SMSWebhookController::class, 'handleDeliveryReport'])
    ->name('sms.delivery.webhook');

// Africa's Talking callback endpoint
Route::post('/africastalking/callback', [AfricasTalkingCallbackController::class, 'handle'])->name('africastalking.callback');
