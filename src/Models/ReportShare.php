<?php

namespace Ibekzod\VisualReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportShare extends Model
{
    protected $table = 'visual_report_shares';

    protected $fillable = [
        'report_id',
        'user_id',
        'can_edit',
        'can_share',
    ];

    protected $casts = [
        'can_edit' => 'boolean',
        'can_share' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = ['created_at', 'updated_at'];

    /**
     * Get the report being shared
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the user the report is shared with
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        return $this->belongsTo($userModel);
    }

    /**
     * Grant edit permission
     */
    public function grantEditPermission(): self
    {
        $this->update(['can_edit' => true]);
        return $this;
    }

    /**
     * Revoke edit permission
     */
    public function revokeEditPermission(): self
    {
        $this->update(['can_edit' => false]);
        return $this;
    }

    /**
     * Grant share permission
     */
    public function grantSharePermission(): self
    {
        $this->update(['can_share' => true]);
        return $this;
    }

    /**
     * Revoke share permission
     */
    public function revokeSharePermission(): self
    {
        $this->update(['can_share' => false]);
        return $this;
    }

    /**
     * Scope to get shares with edit permission
     */
    public function scopeCanEdit($query)
    {
        return $query->where('can_edit', true);
    }

    /**
     * Scope to get shares with share permission
     */
    public function scopeCanShare($query)
    {
        return $query->where('can_share', true);
    }
}
