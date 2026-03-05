<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = [
        'order_id', 'raised_by', 'against_user',
        'reason', 'description', 'status',
        'resolution', 'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function raisedByUser()
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function againstUser()
    {
        return $this->belongsTo(User::class, 'against_user');
    }

    public function resolvedByUser()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
