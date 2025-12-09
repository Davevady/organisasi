<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $fillable = [
        'uuid',
        'member_code',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'join_date',
        'exit_date',
        'notes',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'join_date' => 'date',
        'exit_date' => 'date',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(MemberPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
