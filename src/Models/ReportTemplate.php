<?php

namespace Ibekzod\VisualReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'report_templates';

    protected $fillable = [
        'name',
        'description',
        'model',
        'dimensions',
        'metrics',
        'filters',
        'default_view',
        'chart_config',
        'icon',
        'category',
        'sort_order',
        'is_active',
        'is_public',
        'created_by',
    ];

    protected $casts = [
        'dimensions' => 'json',
        'metrics' => 'json',
        'filters' => 'json',
        'default_view' => 'json',
        'chart_config' => 'json',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        // Use configurable user model, fallback to Laravel's auth config
        $userModel = config('visual-report-builder.models.user')
            ?? config('auth.providers.users.model', 'App\\Models\\User');

        // Return null relationship if user model is not configured
        if (!$userModel) {
            return $this->belongsTo(User::class, 'created_by');
        }

        return $this->belongsTo($userModel, 'created_by');
    }

    /**
     * Get the report results from this template
     */
    public function results(): HasMany
    {
        return $this->hasMany(ReportResult::class);
    }

    /**
     * Get the filters for this template
     */
    public function templateFilters(): HasMany
    {
        return $this->hasMany(TemplateFilter::class)->orderBy('sort_order');
    }

    /**
     * Scope: Get active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get public templates
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Get templates by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get dimensions with options
     */
    public function getDimensions(): array
    {
        return $this->dimensions ?? [];
    }

    /**
     * Get metrics with options
     */
    public function getMetrics(): array
    {
        return $this->metrics ?? [];
    }

    /**
     * Get filter definitions
     */
    public function getFilters(): array
    {
        return $this->templateFilters()
            ->where('is_active', true)
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'column' => $f->column,
                'label' => $f->label,
                'type' => $f->type,
                'operator' => $f->operator,
                'options' => $f->options,
                'is_required' => $f->is_required,
                'default_value' => $f->default_value,
            ])
            ->toArray();
    }

    /**
     * Get available view types
     */
    public function getViewTypes(): array
    {
        return ['table', 'line', 'bar', 'pie', 'area', 'scatter'];
    }

    /**
     * Get categories
     */
    public static function getCategories(): array
    {
        return self::distinct('category')
            ->whereNotNull('category')
            ->pluck('category')
            ->sort()
            ->toArray();
    }
}
