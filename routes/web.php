<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ActiveUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MeetingController;

// Auth routes
Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register/init',     [AuthController::class, 'registerInit'])->name('register.init');
Route::post('/register/resend',   [AuthController::class, 'registerResend'])->name('register.resend');
Route::post('/register/verify',   [AuthController::class, 'registerVerify'])->name('register.verify');
Route::post('/register/complete', [AuthController::class, 'registerComplete'])->name('register.complete');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

// Forgot / reset password
Route::get('/forgot-password',          [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password/send',    [AuthController::class, 'forgotPasswordSend'])->name('password.send');
Route::post('/forgot-password/verify',  [AuthController::class, 'forgotPasswordVerify'])->name('password.verify');
Route::post('/forgot-password/reset',   [AuthController::class, 'forgotPasswordReset'])->name('password.update');

// Google OAuth routes
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

// Google Calendar refresh-token helper (one-time setup)
Route::get('/admin/google-calendar/connect',  [\App\Http\Controllers\Admin\GoogleCalendarConnectController::class, 'connect']);
Route::get('/admin/google-calendar/callback', [\App\Http\Controllers\Admin\GoogleCalendarConnectController::class, 'callback']);

// Cambio de idioma — disponible sin autenticación
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', [HomeController::class, 'index'])->middleware('auth');

// Reservation routes
Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/form', [ReservationController::class, 'showForm']);
Route::post('/reservations/update', [ReservationController::class, 'update']);
Route::post('/reservations/confirm', [ReservationController::class, 'confirm']);
Route::get('/api/reservations/{code}', [ReservationController::class, 'getByCode']);

// Dashboard route (protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/progress', [DashboardController::class, 'progress'])->name('dashboard.progress');
    Route::get('/dashboard/documents', [DashboardController::class, 'documents'])->name('dashboard.documents');
    Route::get('/dashboard/payments', [DashboardController::class, 'payments'])->name('dashboard.payments');
    Route::get('/dashboard/messages', [DashboardController::class, 'messages'])->name('dashboard.messages');
    Route::post('/dashboard/messages', [DashboardController::class, 'sendMessage'])->name('dashboard.messages.send');
    
    // Contract confirmation route (client can only confirm/sign)
    Route::post('/contract/{reservation}/confirm', [ContractController::class, 'confirm'])->name('contract.confirm');
    
    // Client contract generation routes
    Route::get('/contract/{reservation}/generate', [ContractController::class, 'generate'])->name('contract.generate');
    Route::get('/contract/{reservation}/payment-plan', [ContractController::class, 'generatePaymentPlan'])->name('contract.payment-plan');
    Route::get('/contract/{reservation}/purchase-promise', [ContractController::class, 'generatePurchasePromise'])->name('contract.purchase-promise');
    
    // Budget acceptance route (client views & accepts budget from admin)
    Route::get('/dashboard/budget/{reservation}', [DashboardController::class, 'showBudget'])->name('dashboard.budget');
    Route::post('/dashboard/budget/{reservation}/accept', [DashboardController::class, 'acceptBudget'])->name('dashboard.budget.accept');
    Route::post('/dashboard/budget/{reservation}/observation', [DashboardController::class, 'submitBudgetObservation'])->name('dashboard.budget.observation');
    // Contract (purchase_promise / contract) review cycle from client side
    Route::post('/dashboard/contract/{document}/observation', [DashboardController::class, 'submitContractObservation'])->name('dashboard.contract.observation');
    Route::post('/dashboard/contract/{document}/accept', [DashboardController::class, 'acceptContract'])->name('dashboard.contract.accept');
    
    // Document management routes
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])->name('documents.sign');
    Route::post('/documents/{document}/signnow/sync', [DocumentController::class, 'signnowSync'])->name('documents.signnow.sync');
    // SignNow webhook (public, validated via signature header)
    Route::post('/webhooks/signnow', [DocumentController::class, 'signnowWebhook'])->name('webhooks.signnow')->withoutMiddleware(['auth']);
    Route::post('/documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
    Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
    Route::get('/reservations/{reservation}/documents', [DocumentController::class, 'getDocuments'])->name('reservations.documents');
    Route::post('/reservations/{reservation}/documents/upload', [DocumentController::class, 'upload'])->name('reservations.documents.upload');
    Route::delete('/documents/{document}', [DocumentController::class, 'delete'])->name('documents.delete');
    Route::post('/reservations/{reservation}/documents/initialize', [DocumentController::class, 'initialize'])->name('reservations.documents.initialize');
    
    // Contract approval workflow routes
    Route::post('/reservations/{reservation}/save-observations', [DocumentController::class, 'saveObservations'])->name('reservations.save.observations');
    Route::post('/reservations/{reservation}/mark-conforme', [DocumentController::class, 'markConforme'])->name('reservations.mark.conforme');
    
    // Payment submission routes (client)
    Route::post('/dashboard/payments/{reservation}/submit', [DashboardController::class, 'submitPayment'])->name('dashboard.payments.submit');
    
    // Message routes (client)
    Route::post('/dashboard/messages/send', [DashboardController::class, 'sendMessage'])->name('dashboard.messages.send');

    // Profile (client)
    Route::get('/dashboard/profile',  [DashboardController::class, 'editProfile'])->name('dashboard.profile.edit');
    Route::post('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');

    // Guardados / wishlist
    Route::get('/dashboard/guardados', [DashboardController::class, 'guardados'])->name('dashboard.guardados');

    // Global search (client topbar)
    Route::get('/dashboard/search', [SearchController::class, 'client'])->name('dashboard.search');

    // Notifications (client topbar)
    Route::get('/dashboard/notifications',       [NotificationController::class, 'client'])->name('dashboard.notifications');
    Route::post('/dashboard/notifications/read', [NotificationController::class, 'read'])->name('dashboard.notifications.read');

    // Acuerdos (documentos firmables)
    Route::get('/dashboard/acuerdos',  [DashboardController::class, 'acuerdos'])->name('dashboard.acuerdos');

    // Calendario
    Route::get('/dashboard/calendario', [DashboardController::class, 'calendario'])->name('dashboard.calendario');

    // Videollamadas (Google Meet)
    Route::get('/api/meetings/availability', [MeetingController::class, 'availability'])->name('meetings.availability');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
});

// Active users routes
Route::get('/api/active-users', [ActiveUserController::class, 'getActiveUsersCount']);
Route::post('/api/update-last-seen', [ActiveUserController::class, 'updateLastSeen']);

// Home page API routes
Route::get('/api/units/{unitId}', [HomeController::class, 'getUnitDetails']);
Route::post('/api/units/{unitId}/view', [HomeController::class, 'recordView']);
Route::post('/api/units/filter', [HomeController::class, 'filterUnits']);
Route::post('/api/wishlist/toggle/{unitId}', [HomeController::class, 'toggleWishlist'])->middleware('auth')->name('wishlist.toggle');
Route::get('/property-pdf/{unitId}', [HomeController::class, 'propertyPdf'])->name('property.pdf');

// Admin routes
Route::prefix('admin')->middleware(['admin'])->group(function () {

    // Global search (admin topbar)
    Route::get('/search', [SearchController::class, 'admin'])->name('admin.search');

    // Notifications (admin topbar)
    Route::get('/notifications',       [NotificationController::class, 'admin'])->name('admin.notifications');
    Route::post('/notifications/read', [NotificationController::class, 'read'])->name('admin.notifications.read');

    Route::get('/units', [AdminController::class, 'units'])->name('admin.units');
    Route::get('/units/create', [AdminController::class, 'createUnit'])->name('admin.units.create');
    Route::post('/units', [AdminController::class, 'storeUnit'])->name('admin.units.store');
    Route::get('/units/{unit}', [AdminController::class, 'editUnit'])->name('admin.units.edit');
    Route::put('/units/{unit}', [AdminController::class, 'updateUnit'])->name('admin.units.update');
    Route::delete('/units/{unit}', [AdminController::class, 'deleteUnit'])->name('admin.units.delete');
    Route::post('/units/{unit}/toggle-public', [AdminController::class, 'togglePublicUnit'])->name('admin.units.toggle-public');
    Route::delete('/units/{unit}/images/{image}', [AdminController::class, 'deleteUnitImage'])->name('admin.units.images.delete');
    Route::post('/units/{unit}/images/reorder', [AdminController::class, 'reorderUnitImages'])->name('admin.units.images.reorder');
    Route::post('/units/{unit}/images/upload', [AdminController::class, 'uploadUnitImages'])->name('admin.units.images.upload');
    
    Route::get('/deals', [AdminController::class, 'deals'])->name('admin.deals');
    Route::post('/deals', [AdminController::class, 'storeDeal'])->name('admin.deals.store');
    Route::put('/deals/{deal}', [AdminController::class, 'updateDeal'])->name('admin.deals.update');
    Route::delete('/deals/{deal}', [AdminController::class, 'deleteDeal'])->name('admin.deals.delete');
    
    Route::get('/transactions-report', [AdminController::class, 'transactionsReport'])->name('admin.transactions-report');
    Route::get('/profiles', [AdminController::class, 'profiles'])->name('admin.profiles');

    Route::get('/agents', [AdminController::class, 'agents'])->name('admin.agents');
    Route::post('/agents', [AdminController::class, 'storeAgent'])->name('admin.agents.store');
    Route::put('/agents/{agent}', [AdminController::class, 'updateAgent'])->name('admin.agents.update');
    Route::delete('/agents/{agent}', [AdminController::class, 'deleteAgent'])->name('admin.agents.delete');
    Route::post('/agents/{agent}/units', [AdminController::class, 'assignBrokerUnits'])->name('admin.agents.units');

    Route::get('/communication', [AdminController::class, 'communication'])->name('admin.communication');
    Route::get('/communication/conversation/{id}', [AdminController::class, 'communicationConversation'])->name('admin.communication.conversation');
    Route::get('/extras', [AdminController::class, 'extras'])->name('admin.extras');
    Route::get('/data-export', [AdminController::class, 'dataExport'])->name('admin.data-export');
    Route::get('/email-templates', [AdminController::class, 'emailTemplates'])->name('admin.email-templates');
    Route::get('/registration-fields', [AdminController::class, 'registrationFields'])->name('admin.registration-fields');
    Route::get('/menu', [AdminController::class, 'menu'])->name('admin.menu');
    Route::get('/landing', [AdminController::class, 'landing'])->name('admin.landing');
    Route::get('/social-chat', [AdminController::class, 'socialChat'])->name('admin.social-chat');
    Route::get('/survey', [AdminController::class, 'survey'])->name('admin.survey');
    Route::get('/cta-cards', [AdminController::class, 'ctaCards'])->name('admin.cta-cards');
    Route::get('/theme', [AdminController::class, 'theme'])->name('admin.theme');
    Route::get('/account', [AdminController::class, 'account'])->name('admin.account');

    // Profile (admin)
    Route::get('/profile',  [AdminController::class, 'editProfile'])->name('admin.profile.edit');
    Route::post('/profile', [AdminController::class, 'updateProfile'])->name('admin.profile.update');

    // CRM Operativo
    Route::get('/crm/dashboard',    [AdminController::class, 'crmDashboard'])->name('admin.crm.dashboard');
    Route::get('/crm/expedientes',  [AdminController::class, 'crmExpedientes'])->name('admin.crm.expedientes');
    Route::post('/crm/expedientes/{reservation}/budget',        [AdminController::class, 'saveBudget'])->name('admin.crm.budget.save');
    Route::post('/crm/expedientes/{reservation}/budget/revert', [AdminController::class, 'revertBudget'])->name('admin.crm.budget.revert');
    // Admin contract management (upload new version, reply observations)
    Route::post('/crm/contract/{document}/upload', [AdminController::class, 'uploadModifiedContract'])->name('admin.crm.contract.upload');
    Route::post('/crm/contract/{document}/reply',  [AdminController::class, 'replyContractObservation'])->name('admin.crm.contract.reply');
    Route::get('/crm/documentos',   [AdminController::class, 'crmDocumentos'])->name('admin.crm.documentos');
    Route::get('/crm/contratos',    [AdminController::class, 'crmContratos'])->name('admin.crm.contratos');
    Route::get('/crm/proyectos',    [AdminController::class, 'crmProyectos'])->name('admin.crm.proyectos');
    Route::get('/crm/postventa',    [AdminController::class, 'crmPostventa'])->name('admin.crm.postventa');
    Route::get('/crm/aprobaciones', [AdminController::class, 'crmAprobaciones'])->name('admin.crm.aprobaciones');
    Route::get('/crm/tareas',       [AdminController::class, 'crmTareas'])->name('admin.crm.tareas');
    Route::get('/crm/pagos/{id}', [AdminController::class, 'crmPagos'])->name('admin.crm.pagos');

    // CRM nuevas páginas (sin backend, sólo vista)
    Route::get('/crm/avance-obra',  [AdminController::class, 'crmAvanceObra'])->name('admin.crm.avance-obra');
    Route::get('/crm/plantillas',   [AdminController::class, 'crmPlantillas'])->name('admin.crm.plantillas');
    Route::get('/crm/anuncios',     [AdminController::class, 'crmAnuncios'])->name('admin.crm.anuncios');
    Route::get('/crm/proyectos/{id}', [AdminController::class, 'crmProyectoDetalle'])->name('admin.crm.proyecto.detalle');
    Route::get('/crm/expedientes/{id}', [AdminController::class, 'crmExpedienteDetalle'])->name('admin.crm.expediente.detalle');

    // KYC verification (approve/reject user registration docs)
    Route::post('/users/{userId}/verify-kyc', [AdminController::class, 'verifyUserKyc'])->name('admin.users.verify-kyc');

    // CRM acciones desde modales
    Route::post('/crm/reservation/create', [AdminController::class, 'createReservationQuick'])->name('admin.crm.reservation.create');
    Route::post('/crm/document/upload',    [AdminController::class, 'uploadDocumentQuick'])->name('admin.crm.document.upload');
    Route::post('/crm/payment/create',     [AdminController::class, 'createPaymentQuick'])->name('admin.crm.payment.create');
    Route::get('/crm/export',              [AdminController::class, 'exportResource'])->name('admin.crm.export');
    Route::post('/crm/export/request-code', [AdminController::class, 'requestExportCode'])->name('admin.crm.export.request');
    Route::post('/crm/export/resend-code',  [AdminController::class, 'resendExportCode'])->name('admin.crm.export.resend');
    Route::post('/crm/export/verify',       [AdminController::class, 'verifyExportCode'])->name('admin.crm.export.verify');
    Route::post('/crm/message/send',       [AdminController::class, 'sendMessageQuick'])->name('admin.crm.message.send');

    // CRM Tareas CRUD
    Route::post('/crm/tareas',                    [AdminController::class, 'storeTask'])->name('admin.crm.tareas.store');
    Route::post('/crm/tareas/{task}/complete',    [AdminController::class, 'completeTask'])->name('admin.crm.tareas.complete');
    Route::post('/crm/tareas/{task}/status',      [AdminController::class, 'updateTaskStatus'])->name('admin.crm.tareas.status');
    Route::delete('/crm/tareas/{task}',           [AdminController::class, 'deleteTask'])->name('admin.crm.tareas.delete');

    // CRM Aprobaciones CRUD
    Route::post('/crm/aprobaciones',                       [AdminController::class, 'storeApproval'])->name('admin.crm.aprobaciones.store');
    Route::post('/crm/aprobaciones/{approval}/decide',     [AdminController::class, 'decideApproval'])->name('admin.crm.aprobaciones.decide');
    Route::delete('/crm/aprobaciones/{approval}',          [AdminController::class, 'deleteApproval'])->name('admin.crm.aprobaciones.delete');

    // CRM Postventa CRUD
    Route::post('/crm/postventa',                  [AdminController::class, 'storeAftersale'])->name('admin.crm.postventa.store');
    Route::put('/crm/postventa/{aftersale}',       [AdminController::class, 'updateAftersale'])->name('admin.crm.postventa.update');
    Route::delete('/crm/postventa/{aftersale}',    [AdminController::class, 'deleteAftersale'])->name('admin.crm.postventa.delete');

    // CRM Proyectos CRUD
    Route::post('/crm/proyectos',                  [AdminController::class, 'storeProject'])->name('admin.crm.proyectos.store');
    Route::put('/crm/proyectos/{project}',         [AdminController::class, 'updateProject'])->name('admin.crm.proyectos.update');
    Route::delete('/crm/proyectos/{project}',      [AdminController::class, 'deleteProject'])->name('admin.crm.proyectos.delete');

    // CRM Plantillas CRUD
    Route::post('/crm/plantillas',                          [AdminController::class, 'storeTemplate'])->name('admin.crm.plantillas.store');
    Route::put('/crm/plantillas/{template}',                [AdminController::class, 'updateTemplate'])->name('admin.crm.plantillas.update');
    Route::delete('/crm/plantillas/{template}',             [AdminController::class, 'deleteTemplate'])->name('admin.crm.plantillas.delete');
    Route::post('/crm/plantillas/{template}/duplicate',     [AdminController::class, 'duplicateTemplate'])->name('admin.crm.plantillas.duplicate');
    Route::get('/crm/plantillas/{template}/data',           [AdminController::class, 'getTemplate'])->name('admin.crm.plantillas.data');
    Route::post('/crm/plantillas/{template}/test',          [AdminController::class, 'sendTestTemplate'])->name('admin.crm.plantillas.test');

    // CRM Automatizaciones CRUD
    Route::post('/crm/automatizaciones',                          [AdminController::class, 'storeAutomation'])->name('admin.crm.automatizaciones.store');
    Route::put('/crm/automatizaciones/{automation}',              [AdminController::class, 'updateAutomation'])->name('admin.crm.automatizaciones.update');
    Route::delete('/crm/automatizaciones/{automation}',           [AdminController::class, 'deleteAutomation'])->name('admin.crm.automatizaciones.delete');
    Route::post('/crm/automatizaciones/{automation}/toggle',      [AdminController::class, 'toggleAutomation'])->name('admin.crm.automatizaciones.toggle');
    Route::post('/crm/automatizaciones/{automation}/run',         [AdminController::class, 'runAutomation'])->name('admin.crm.automatizaciones.run');
    Route::get('/crm/automatizaciones/{automation}/data',         [AdminController::class, 'getAutomation'])->name('admin.crm.automatizaciones.data');

    // CRM Channel settings
    Route::post('/crm/canales',                             [AdminController::class, 'updateChannels'])->name('admin.crm.canales.update');
    
    // Contract generation routes for admin
    Route::get('/crm/contract/{reservation}/generate', [AdminController::class, 'generateContract'])->name('admin.crm.contract.generate');
    Route::get('/crm/contract/{reservation}/payment-plan', [AdminController::class, 'generatePaymentPlan'])->name('admin.crm.contract.payment-plan');
    Route::get('/crm/contract/{reservation}/purchase-promise', [AdminController::class, 'generatePurchasePromise'])->name('admin.crm.contract.purchase-promise');
    
    // API Routes for Payment Management
    Route::get('/api/reservations/{id}', [AdminController::class, 'getReservation']);
    Route::get('/api/reservations/{id}/payments', [AdminController::class, 'getReservationPayments']);
    Route::post('/api/reservations/{id}/payments', [AdminController::class, 'createPayment']);
    Route::put('/api/payments/{id}', [AdminController::class, 'updatePayment']);
    Route::delete('/api/payments/{id}', [AdminController::class, 'deletePayment']);
    Route::post('/api/payments/{id}/mark-paid', [AdminController::class, 'markPaymentAsPaid']);
    
    // Payment approval routes
    Route::post('/payments/{payment}/approve', [AdminController::class, 'approvePayment'])->name('admin.payments.approve');
});


