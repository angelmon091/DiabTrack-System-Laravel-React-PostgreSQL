<?php

use App\Http\Controllers\Admin\ApiUsageController;
use App\Http\Controllers\Admin\DoctorApprovalController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CaregiverController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PatientNotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Tracking\ActivityLogController;
use App\Http\Controllers\Tracking\NutritionLogController;
use App\Http\Controllers\Tracking\SymptomLogController;
use App\Http\Controllers\Tracking\VitalSignController;
use App\Services\TipService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'homeUrl' => url('/'),
        'loginUrl' => route('login', absolute: false),
        'registerUrl' => route('register', absolute: false),
        'dashboardUrl' => route('dashboard', absolute: false),
        'ogImageUrl' => asset('og-image.jpg'),
        'year' => now()->year,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/verify-email/{token}', [ProfileController::class, 'verifyEmail'])->name('profile.email.verify');
    Route::delete('/profile/unlink/{linkedUser}', [ProfileController::class, 'unlinkCarer'])->name('profile.unlink');

    // Notificaciones
    Route::post('/notifications/{notification}/read', [PatientNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [PatientNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::delete('/notifications/all', [PatientNotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::delete('/notifications/{notification}', [PatientNotificationController::class, 'destroy'])->name('notifications.destroy');

    // Onboarding Routes
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::get('/onboarding/patient', [OnboardingController::class, 'showPatientForm'])->name('onboarding.patient');
    Route::post('/onboarding/patient', [OnboardingController::class, 'storePatient'])->name('onboarding.patient.store');
    Route::get('/onboarding/caregiver', [OnboardingController::class, 'showCaregiverForm'])->name('onboarding.caregiver');
    Route::post('/onboarding/caregiver', [OnboardingController::class, 'storeCaregiver'])->name('onboarding.caregiver.store');
    Route::get('/onboarding/doctor', [OnboardingController::class, 'showDoctorForm'])->name('onboarding.doctor');
    Route::post('/onboarding/doctor', [OnboardingController::class, 'storeDoctor'])->name('onboarding.doctor.store');

    // Patient-specific routes
    Route::middleware(['role:paciente'])->group(function () {
        Route::post('/dashboard/weight', [DashboardController::class, 'storeWeight'])->name('dashboard.weight.store');
        Route::post('/dashboard/invite', [DashboardController::class, 'generateInviteCode'])->name('dashboard.invite');

        Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:60,1')->name('search');

        Route::prefix('tracking')->middleware('throttle:30,1')->name('tracking.')->group(function () {
            Route::get('/summary', [DashboardController::class, 'summary'])->name('summary');

            Route::get('/vitals', [VitalSignController::class, 'create'])->name('vital.create');
            Route::post('/vitals', [VitalSignController::class, 'store'])->name('vital.store');

            Route::get('/activity', [ActivityLogController::class, 'create'])->name('activity.create');
            Route::post('/activity', [ActivityLogController::class, 'store'])->name('activity.store');

            Route::get('/nutrition', [NutritionLogController::class, 'index'])->name('nutrition.index');
            Route::get('/nutrition/create', [NutritionLogController::class, 'create'])->name('nutrition.create');
            Route::post('/nutrition', [NutritionLogController::class, 'store'])->name('nutrition.store');

            Route::get('/symptoms', [SymptomLogController::class, 'create'])->name('symptom.create');
            Route::post('/symptoms', [SymptomLogController::class, 'store'])->name('symptom.store');
        });
    });

    // Caregiver routes
    Route::middleware(['role:cuidador'])->prefix('caregiver')->name('caregiver.')->group(function () {
        Route::get('/', [CaregiverController::class, 'dashboard'])->name('dashboard');
        Route::get('/link', [CaregiverController::class, 'showLinkForm'])->name('link');
        Route::post('/link', [CaregiverController::class, 'linkPatient'])->name('link.store');
        Route::get('/patient/{patient}', [CaregiverController::class, 'showPatient'])->name('patient.show');
        Route::get('/patient/{patient}/vital/create', [CaregiverController::class, 'createVital'])->name('patient.vital.create');
        Route::post('/patient/{patient}/vital', [CaregiverController::class, 'storeVital'])->name('patient.vital.store');
        Route::delete('/patient/{patient}/unlink', [CaregiverController::class, 'unlinkPatient'])->name('patient.unlink');
    });

    // Doctor routes
    Route::middleware(['role:médico'])->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/', [DoctorController::class, 'dashboard'])->name('dashboard');
        Route::middleware('doctor.approved')->group(function () {
            Route::get('/link', [DoctorController::class, 'showLinkForm'])->name('link');
            Route::post('/link', [DoctorController::class, 'linkPatient'])->name('link.store');
        });
        Route::get('/patient/{patient}', [DoctorController::class, 'showPatient'])->name('patient.show');
        Route::patch('/patient/{patient}/targets', [DoctorController::class, 'updateTargets'])->name('patient.targets.update');
        Route::delete('/patient/{patient}/unlink', [DoctorController::class, 'unlinkPatient'])->name('patient.unlink');
    });

});

require __DIR__.'/auth.php';

// Admin Routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Dashboard', [
            'adminNavigation' => [
                'dashboardUrl' => route('admin.dashboard', absolute: false),
                'usersUrl' => route('admin.users.index', absolute: false),
                'rolesUrl' => route('admin.roles.index', absolute: false),
                'doctorsUrl' => route('admin.doctors.index', absolute: false),
                'apiUsageUrl' => route('admin.api-usage.index', absolute: false),
            ],
        ]);
    })->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('doctors', [DoctorApprovalController::class, 'index'])->name('doctors.index');
    Route::patch('doctors/{doctorProfile}/approve', [DoctorApprovalController::class, 'approve'])->name('doctors.approve');
    Route::patch('doctors/{doctorProfile}/reject', [DoctorApprovalController::class, 'reject'])->name('doctors.reject');
    Route::get('api-usage', [ApiUsageController::class, 'index'])->name('api-usage.index');
});

if (app()->environment('local')) {
    Route::get('/test-tip', function (TipService $tipService) {
        return response()->json($tipService->generarTip([
            'tipo_diabetes' => 2,
            'edad' => 45,
            'glucosa' => 190,
            'hba1c' => 8.2,
            'imc' => 28.5,
        ]));
    });
}
