<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\MekariAuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{id}', [UserController::class, 'show'])->name('show');
});


Route::get('/login', [MekariAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [MekariAuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [MekariAuthController::class, 'logout'])->name('logout');
Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/', [RoomController::class, 'index'])->name('index');
    Route::get('/expired', [RoomController::class, 'listExpired'])->name('expired');
    Route::get('/{id}', [RoomController::class, 'show'])->name('show');
    Route::put('/{id}/rename', [RoomController::class, 'rename'])->name('rename');
    Route::get('/{id}/histories', [RoomController::class, 'histories'])->name('histories');
    Route::get('/{id}/participants', [RoomController::class, 'participants'])->name('participants');
    Route::get('/{id}/agents/assignable', [RoomController::class, 'assignableAgents'])->name('assignable-agents');
    Route::post('/{id}/agents/{userId}', [RoomController::class, 'assignAgent'])->name('assign-agent');
    Route::post('/auto-takeover', [RoomController::class, 'autoTakeover'])->name('auto-takeover');
    Route::post('/{id}/takeover', [RoomController::class, 'takeover'])->name('takeover');
    Route::put('/{id}/mark-read', [RoomController::class, 'markAllAsRead'])->name('mark-read');
    Route::post('/{id}/handover/{userId}', [RoomController::class, 'handover'])->name('handover');
    Route::put('/resolve-expired', [RoomController::class, 'resolveExpired'])->name('resolve-expired');
    Route::put('/{id}/resolve', [RoomController::class, 'resolve'])->name('resolve');
    Route::post('/{id}/tags', [RoomController::class, 'addTag'])->name('add-tag');
    Route::delete('/{id}/tags', [RoomController::class, 'removeTag'])->name('remove-tag');
    Route::get('/{id}/messages', [RoomController::class, 'messages'])->name('messages');
    Route::post('/{id}/messages', [RoomController::class, 'sendMessage'])->name('send-message');
    Route::post('/{id}/send-whatsapp', [RoomController::class, 'sendWhatsAppMessage'])->name('send-whatsapp');
    Route::post('/{id}/send-whatsapp-bot', [RoomController::class, 'sendWhatsAppBotMessage'])->name('send-whatsapp-bot');
    Route::post('/{id}/send-interactive', [RoomController::class, 'sendInteractiveMessage'])->name('send-interactive');
    Route::post('/{id}/send-hsm', [RoomController::class, 'sendHsmMessage'])->name('send-hsm');
});

Route::prefix('interactions')->name('interactions.')->group(function () {
    Route::get('/message', [RoomController::class, 'showMessageInteractions'])->name('message.index');
    Route::put('/message', [RoomController::class, 'updateMessageInteractions'])->name('message.update');
    Route::get('/message/data', [RoomController::class, 'getMessageInteractions'])->name('message.get');
    
    Route::get('/room', [RoomController::class, 'showRoomInteractions'])->name('room.index');
    Route::put('/room', [RoomController::class, 'updateRoomInteractions'])->name('room.update');
    Route::get('/room/data', [RoomController::class, 'getRoomInteractions'])->name('room.get');
});

Route::prefix('integrations')->name('integrations.')->group(function () {
    Route::get('/', [IntegrationController::class, 'index'])->name('index');
    Route::get('/{channelId}', [IntegrationController::class, 'show'])->name('show');
});

// Route::prefix('rooms')->name('rooms.')->group(function () {
//     Route::get('/', [RoomController::class, 'index'])->name('index');
//     Route::get('/expired', [RoomController::class, 'listExpired'])->name('expired');
//     Route::get('/specific-info', [RoomController::class, 'specificInfo'])->name('specific-info');
    
//     Route::post('/auto-takeover', [RoomController::class, 'autoTakeover'])->name('auto-takeover');
//     Route::put('/resolve-expired', [RoomController::class, 'resolveExpired'])->name('resolve-expired');
    
//     Route::get('/{id}', [RoomController::class, 'show'])->name('show');
//     Route::put('/{id}/rename', [RoomController::class, 'rename'])->name('rename');
//     Route::get('/{id}/histories', [RoomController::class, 'histories'])->name('histories');
//     Route::get('/{id}/participants', [RoomController::class, 'participants'])->name('participants');
//     Route::get('/{id}/messages', [RoomController::class, 'messages'])->name('messages');
    
//     Route::post('/{id}/takeover', [RoomController::class, 'takeover'])->name('takeover');
//     Route::put('/{id}/mark-read', [RoomController::class, 'markAllAsRead'])->name('mark-read');
//     Route::put('/{id}/resolve', [RoomController::class, 'resolve'])->name('resolve');
    
//     Route::get('/{id}/agents/assignable', [RoomController::class, 'assignableAgents'])->name('assignable-agents');
//     Route::post('/{id}/agents/{userId}', [RoomController::class, 'assignAgent'])->name('assign-agent');
//     Route::post('/{id}/handover/{userId}', [RoomController::class, 'handover'])->name('handover');
    
//     Route::post('/{id}/tags', [RoomController::class, 'addTag'])->name('add-tag');
//     Route::delete('/{id}/tags', [RoomController::class, 'removeTag'])->name('remove-tag');
    
//     Route::post('/{id}/messages', [RoomController::class, 'sendMessage'])->name('send-message');
// });

// Route::prefix('integrations')->name('integrations.')->group(function () {
//     Route::get('/', [IntegrationController::class, 'index'])->name('index');
//     Route::get('/{channelId}', [IntegrationController::class, 'show'])->name('show');
//     Route::get('/{channelId}/messages', [IntegrationController::class, 'messages'])->name('messages');
    
//     Route::post('/send/whatsapp', [IntegrationController::class, 'sendWhatsAppMessage'])->name('send.whatsapp');
//     Route::post('/send/whatsapp/bot', [IntegrationController::class, 'sendWhatsAppBotMessage'])->name('send.whatsapp.bot');
//     Route::post('/send/interactive', [IntegrationController::class, 'sendInteractiveMessage'])->name('send.interactive');
//     Route::post('/send/hsm', [IntegrationController::class, 'sendHsmMessage'])->name('send.hsm');
// });