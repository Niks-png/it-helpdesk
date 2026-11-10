<?php

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'category', 'title', 'description', 'screenshot', 'status'])]
class Ticket extends Model
{
    protected $attributes = [
        'status' => 'new',
    ];

    protected function casts(): array
    {
        return [
            'category' => TicketCategory::class,
            'status' => TicketStatus::class,
        ];
    }

    protected static function booted(): void
    {
        // shortcut: the number comes from the database id, so it does not restart at 0001 each year; add a per-year counter if that is required.
        static::created(function (Ticket $ticket) {
            $ticket->number = sprintf('IT-%d-%04d', $ticket->created_at->year, $ticket->id);
            $ticket->saveQuietly();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
