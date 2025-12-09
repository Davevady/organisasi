<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPayment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'payment_code',
        'member_id',
        'cash_transaction_id',
        'period',
        'amount',
        'payment_date',
        'due_date',
        'status',
        'payment_method',
        'notes',
        'recorded_by',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'due_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function cashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopePeriod($query, string $period)
    {
        return $query->where('period', $period);
    }
}
