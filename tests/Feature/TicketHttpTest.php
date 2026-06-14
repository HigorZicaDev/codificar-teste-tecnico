<?php

use App\Enums\TicketStatus;
use App\Models\Owner;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

// --- index ---

test('lists tickets on index page', function () {
    Ticket::factory(3)->create();

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Index')
            ->has('tickets.data', 3)
            ->has('filters')
            ->has('owners')
        );
});

test('filters tickets by status', function () {
    Ticket::factory(2)->create(['status' => TicketStatus::Open]);
    Ticket::factory(3)->create(['status' => TicketStatus::Closed]);

    $this->get('/?status=open')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Index')
            ->has('tickets.data', 2)
        );
});

// --- create / store ---

test('shows create form', function () {
    $this->get('/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Create')
            ->has('owners')
        );
});

test('creates ticket successfully with explicit owner', function () {
    $owner = Owner::factory()->create();

    $this->post('/', [
        'title' => 'Test Ticket',
        'description' => 'Test description',
        'priority' => 'high',
        'date_start' => now()->toDateTimeString(),
        'owner_id' => $owner->id,
    ])->assertStatus(302)->assertSessionHas('success');

    $this->assertDatabaseHas('tickets', ['title' => 'Test Ticket', 'owner_id' => $owner->id]);
});

test('creates ticket with auto-assignment when owner_id is absent', function () {
    $owner = Owner::factory()->create();

    $this->post('/', [
        'title' => 'Auto Ticket',
        'description' => 'Description',
        'priority' => 'medium',
        'date_start' => now()->toDateTimeString(),
    ])->assertStatus(302)->assertSessionHas('success');

    $this->assertDatabaseHas('tickets', ['title' => 'Auto Ticket', 'owner_id' => $owner->id]);
});

test('validates required fields on create', function () {
    $this->post('/', [])
        ->assertSessionHasErrors(['title', 'description', 'priority', 'date_start']);
});

// --- show / edit ---

test('shows ticket detail', function () {
    $ticket = Ticket::factory()->create();

    $this->get("/{$ticket->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Show')
            ->where('ticket.id', $ticket->id)
        );
});

test('shows edit form', function () {
    $ticket = Ticket::factory()->create();

    $this->get("/{$ticket->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Edit')
            ->where('ticket.id', $ticket->id)
            ->has('owners')
        );
});

// --- update ---

test('updates ticket successfully', function () {
    $ticket = Ticket::factory()->create();

    $this->put("/{$ticket->id}", ['title' => 'Updated Title'])
        ->assertRedirect("/{$ticket->id}")
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'title' => 'Updated Title']);
});

// --- owner assignment ---

test('manually assigns owner to ticket', function () {
    $ticket = Ticket::factory()->create();
    $newOwner = Owner::factory()->create();

    $this->put("/{$ticket->id}/owner", ['owner_id' => $newOwner->id])
        ->assertRedirect("/{$ticket->id}")
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'owner_id' => $newOwner->id]);
});

test('auto-assigns owner with fewest open tickets (resolved tickets ignored)', function () {
    $busy = Owner::factory()->create();
    $free = Owner::factory()->create();
    $ticket = Ticket::factory()->create(['owner_id' => $busy->id]);

    Ticket::factory(3)->create(['owner_id' => $busy->id, 'status' => TicketStatus::Open]);
    Ticket::factory(5)->create(['owner_id' => $free->id, 'status' => TicketStatus::Resolved]);

    $this->put("/{$ticket->id}/owner/auto")
        ->assertRedirect("/{$ticket->id}")
        ->assertSessionHas('success');

    expect($ticket->fresh()->owner_id)->toBe($free->id);
});

test('auto-assign returns error flash when no owners exist', function () {
    $ticket = Ticket::factory()->create();

    $this->mock(TicketService::class)
        ->shouldReceive('autoAssignOwner')
        ->andThrow(new DomainException('No owners available for assignment.'));

    $this->from("/{$ticket->id}")
        ->put("/{$ticket->id}/owner/auto")
        ->assertStatus(302)
        ->assertSessionHas('error');
});
