<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TicketController::class, 'index'])->name('tickets.index');
Route::get('/create', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
Route::get('/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
Route::match(['put', 'patch'], '/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
Route::put('/{ticket}/owner', [TicketController::class, 'assignOwner'])->name('tickets.owner.assign');
Route::put('/{ticket}/owner/auto', [TicketController::class, 'autoAssignOwner'])->name('tickets.owner.auto-assign');
