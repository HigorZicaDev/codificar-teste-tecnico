<?php

namespace App\Services;

use App\Models\Owner;
use DomainException;

class OwnerAssignmentService
{
    /**
     * Resolve the owner with the fewest open tickets.
     * Tiebreaker: lowest owner ID.
     *
     * @throws DomainException when no owners exist
     */
    public function resolve(): Owner
    {
        $owner = Owner::query()
            ->withCount(['tickets as open_tickets_count' => fn ($q) => $q->open()])
            ->orderBy('open_tickets_count')
            ->orderBy('id')
            ->first();

        if ($owner === null) {
            throw new DomainException('No owners available for assignment.');
        }

        return $owner;
    }
}
