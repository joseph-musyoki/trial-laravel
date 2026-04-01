<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // Priority order for sorting (high → medium → low)
    public const PRIORITY_ORDER = ['high' => 1, 'medium' => 2, 'low' => 3];

    // Valid status transitions
    public const STATUS_TRANSITIONS = [
        'pending'     => 'in_progress',
        'in_progress' => 'done',
    ];

    // Check if a given status is a valid next step from current status.
    public function canTransitionTo(string $newStatus): bool
    {
        return isset(self::STATUS_TRANSITIONS[$this->status])
            && self::STATUS_TRANSITIONS[$this->status] === $newStatus;
    }

    // Scope: filter by status if provided.
    public function scopeFilterByStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }

    // Scope: sort by priority (high → low) then due_date ascending.
    public function scopeSortByPriorityAndDate($query)
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'high' THEN 1
                WHEN 'medium' THEN 2
                WHEN 'low' THEN 3
            END ASC
        ")->orderBy('due_date', 'asc');
    }
}