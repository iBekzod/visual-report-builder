<?php

namespace Ibekzod\VisualReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ReportResult extends Model
{
    use SoftDeletes;

    protected $table = 'report_results';

    protected $fillable = [
        'report_template_id',
        'user_id',
        'name',
        'description',
        'applied_filters',
        'view_config',
        'view_type',
        'data',
        'executed_at',
        'execution_time_ms',
        'is_favorite',
        'view_count',
        'last_viewed_at',
    ];

    protected $casts = [
        'applied_filters' => 'json',
        'view_config' => 'json',
        'data' => 'json',
        'executed_at' => 'datetime',
        'is_favorite' => 'boolean',
        'view_count' => 'integer',
        'last_viewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the template this result is from
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    /**
     * Get the user who created this report result
     */
    public function user(): BelongsTo
    {
        // Use configurable user model, fallback to Laravel's auth config
        $userModel = config('visual-report-builder.models.user')
            ?? config('auth.providers.users.model', 'App\\Models\\User');

        // Return null relationship if user model is not configured
        if (!$userModel) {
            return $this->belongsTo(User::class);
        }

        return $this->belongsTo($userModel);
    }

    /**
     * Mark as favorite
     */
    public function markAsFavorite(): self
    {
        $this->update(['is_favorite' => true]);
        return $this;
    }

    /**
     * Remove from favorite
     */
    public function removeFromFavorite(): self
    {
        $this->update(['is_favorite' => false]);
        return $this;
    }

    /**
     * Increment view count (atomic operation)
     */
    public function recordView(): self
    {
        $this->update([
            'view_count' => DB::raw('view_count + 1'),
            'last_viewed_at' => now(),
        ]);
        return $this;
    }

    /**
     * Scope: Get favorites
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope: Get by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get by template
     */
    public function scopeByTemplate($query, $templateId)
    {
        return $query->where('report_template_id', $templateId);
    }

    /**
     * Scope: Get recently viewed
     */
    public function scopeRecentlyViewed($query, $days = 7)
    {
        return $query->where('last_viewed_at', '>=', now()->subDays($days))
            ->orderBy('last_viewed_at', 'desc');
    }

    /**
     * Get view types
     */
    public static function getAvailableViewTypes(): array
    {
        return ['table', 'line', 'bar', 'pie', 'area', 'scatter'];
    }
}
