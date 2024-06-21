<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Builder|self forUser(int $userId)
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['source_card_id', 'destination_card_id', 'amount'];

    public function sourceCard(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'source_card_id');
    }

    public function destinationCard(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'destination_card_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'transaction_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('sourceCard.account.user', function ($query) use ($userId) {
            $query->where('id', $userId);
        })->orWhereHas('destinationCard.account.user', function ($query) use ($userId) {
            $query->where('id', $userId);
        });
    }
}
