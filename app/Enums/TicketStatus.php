<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function isOpen(): bool
    {
        return in_array($this, [self::Open, self::InProgress]);
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Resolved, self::Closed]);
    }
}
