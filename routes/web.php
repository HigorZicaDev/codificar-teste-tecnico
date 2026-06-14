<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::resource('tickets', TicketController::class)->except(['destroy']);
Route::put('tickets/{ticket}/owner', [TicketController::class, 'assignOwner'])->name('tickets.owner.assign');
Route::put('tickets/{ticket}/owner/auto', [TicketController::class, 'autoAssignOwner'])->name('tickets.owner.auto-assign');
