<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property TicketPriority $priority
 * @property TicketStatus $status
 * @property int $owner_id
 * @property Carbon $date_start
 * @property Carbon|null $opened_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'description', 'priority', 'status', 'owner_id', 'date_start', 'opened_at'])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'date_start' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    /** @param Builder<Ticket> $query */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [TicketStatus::Open, TicketStatus::InProgress]);
    }
}
