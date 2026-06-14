<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use DateTimeInterface;

class TicketService
{
    public function __construct(
        private readonly OwnerAssignmentService $ownerAssignment,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Ticket
    {
        if (empty($data['owner_id'])) {
            $data['owner_id'] = $this->ownerAssignment->resolve()->id;
        }

        $status = $this->resolveStatus($data['status'] ?? TicketStatus::Open);
        $data['status'] = $status;
        $data['opened_at'] = $this->resolveOpenedAt($status);

        return Ticket::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Ticket $ticket, array $data): Ticket
    {
        if (isset($data['status'])) {
            $newStatus = $this->resolveStatus($data['status']);
            $data['status'] = $newStatus;

            if ($newStatus->isOpen() && $ticket->opened_at === null) {
                $data['opened_at'] = now();
            }
        }

        $ticket->update($data);

        return $ticket->fresh();
    }

    public function assignOwner(Ticket $ticket, int $ownerId): Ticket
    {
        $ticket->update(['owner_id' => $ownerId]);

        return $ticket->fresh();
    }

    public function autoAssignOwner(Ticket $ticket): Ticket
    {
        $owner = $this->ownerAssignment->resolve();
        $ticket->update(['owner_id' => $owner->id]);

        return $ticket->fresh();
    }

    private function resolveStatus(TicketStatus|string $status): TicketStatus
    {
        return $status instanceof TicketStatus ? $status : TicketStatus::from($status);
    }

    private function resolveOpenedAt(TicketStatus $status): ?DateTimeInterface
    {
        return $status->isOpen() ? now() : null;
    }
}
