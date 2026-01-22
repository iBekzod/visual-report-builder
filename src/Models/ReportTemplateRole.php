<?php

namespace Ibekzod\VisualReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplateRole extends Model
{
    protected $table = 'report_template_roles';

    protected $fillable = [
        'report_template_id',
        'role_id',
        'can_view',
        'can_export',
        'can_save',
        'can_edit_filters',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_export' => 'boolean',
        'can_save' => 'boolean',
        'can_edit_filters' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the report template this role permission is for
     */
    public function reportTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    /**
     * Get the role
     */
    public function role(): BelongsTo
    {
        // Use configurable role model from visual-report-builder config
        $roleModel = config('visual-report-builder.models.role');

        // Return null relationship if role model is not configured
        if (!$roleModel) {
            return $this->belongsTo(Role::class);
        }

        return $this->belongsTo($roleModel);
    }

    /**
     * Scope: Get permissions where view is allowed
     */
    public function scopeCanView($query)
    {
        return $query->where('can_view', true);
    }

    /**
     * Scope: Get permissions where export is allowed
     */
    public function scopeCanExport($query)
    {
        return $query->where('can_export', true);
    }

    /**
     * Scope: Get permissions where save is allowed
     */
    public function scopeCanSave($query)
    {
        return $query->where('can_save', true);
    }

    /**
     * Scope: Get permissions where edit filters is allowed
     */
    public function scopeCanEditFilters($query)
    {
        return $query->where('can_edit_filters', true);
    }
}
