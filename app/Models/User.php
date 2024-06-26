<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method static Builder|self withTransactionCountsSince(DateTimeInterface $since)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'phone'];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, Account::class);
    }

    public function scopeWithTransactionCountsSince(Builder $query, DateTimeInterface $since): Builder
    {
        return $query->withCount([
            'cards as transactions_count' => function ($query) use ($since) {
                $query->join('transactions', function ($join) use ($since) {
                    $join->on('cards.id', '=', 'transactions.source_card_id')
                        ->orOn('cards.id', '=', 'transactions.destination_card_id')
                        ->where('transactions.created_at', '>=', $since);
                });
            }
        ]);
    }
}
