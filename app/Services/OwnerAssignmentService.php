<?php

namespace App\Services;

use App\Models\Owner;
use App\Models\Ticket;
use DomainException;

class OwnerAssignmentService
{
    /**
     * Priority weights used to break ties by workload (mirrors TicketPriority).
     */
    private const PRIORITY_WEIGHT_SQL = "CASE priority WHEN 'low' THEN 1 WHEN 'medium' THEN 2 WHEN 'high' THEN 3 ELSE 0 END";

    /**
     * Resolve the owner with the fewest open tickets.
     * Tiebreakers, in order: lowest open priority load, then lowest owner ID.
     *
     * @throws DomainException when no owners exist
     */
    public function resolve(): Owner
    {
        $owner = Owner::query()
            ->withCount(['tickets as open_tickets_count' => fn ($q) => $q->open()])
            ->addSelect(['open_priority_weight' => Ticket::query()
                ->selectRaw('COALESCE(SUM('.self::PRIORITY_WEIGHT_SQL.'), 0)')
                ->whereColumn('owner_id', 'owners.id')
                ->open(),
            ])
            ->orderBy('open_tickets_count')
            ->orderBy('open_priority_weight')
            ->orderBy('id')
            ->first();

        if ($owner === null) {
            throw new DomainException('No owners available for assignment.');
        }

        return $owner;
    }
}
