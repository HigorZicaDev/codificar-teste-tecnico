<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Owner;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('considers open and in_progress tickets as open', function () {
    $open = Ticket::factory()->create(['status' => TicketStatus::Open]);
    $inProgress = Ticket::factory()->create(['status' => TicketStatus::InProgress]);

    expect($open->isOpen())->toBeTrue();
    expect($inProgress->isOpen())->toBeTrue();
});

it('considers resolved and closed tickets as not open', function () {
    $resolved = Ticket::factory()->create(['status' => TicketStatus::Resolved]);
    $closed = Ticket::factory()->create(['status' => TicketStatus::Closed]);

    expect($resolved->isOpen())->toBeFalse();
    expect($closed->isOpen())->toBeFalse();
});

it('scope open returns only open and in_progress tickets', function () {
    Ticket::factory()->create(['status' => TicketStatus::Open]);
    Ticket::factory()->create(['status' => TicketStatus::InProgress]);
    Ticket::factory()->create(['status' => TicketStatus::Resolved]);
    Ticket::factory()->create(['status' => TicketStatus::Closed]);

    expect(Ticket::open()->count())->toBe(2);
});

it('casts priority and status to their enum types', function () {
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::Open,
    ]);

    expect($ticket->status)->toBeInstanceOf(TicketStatus::class);
    expect($ticket->priority)->toBeInstanceOf(TicketPriority::class);
    expect($ticket->date_start)->toBeInstanceOf(DateTimeInterface::class);
});

it('belongs to an owner', function () {
    $ticket = Ticket::factory()->create();

    expect($ticket->owner)->toBeInstanceOf(Owner::class);
});
