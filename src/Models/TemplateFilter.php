<?php

namespace Ibekzod\VisualReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateFilter extends Model
{
    protected $table = 'template_filters';

    protected $fillable = [
        'report_template_id',
        'column',
        'label',
        'type',
        'options',
        'operator',
        'is_required',
        'is_active',
        'sort_order',
        'default_value',
    ];

    protected $casts = [
        'options' => 'json',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the template this filter belongs to
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    /**
     * Get available filter types
     */
    public static function getAvailableTypes(): array
    {
        return [
            'text' => 'Text Input',
            'select' => 'Dropdown',
            'multiselect' => 'Multi-Select',
            'date' => 'Single Date',
            'daterange' => 'Date Range',
            'number' => 'Number',
            'checkbox' => 'Checkbox',
        ];
    }

    /**
     * Get available operators
     */
    public static function getAvailableOperators(): array
    {
        return [
            '=' => 'Equals',
            '!=' => 'Not Equals',
            '>' => 'Greater Than',
            '<' => 'Less Than',
            '>=' => 'Greater Than or Equal',
            '<=' => 'Less Than or Equal',
            'in' => 'In List',
            'not_in' => 'Not In List',
            'like' => 'Contains',
            'not_like' => 'Does Not Contain',
            'between' => 'Between',
            'is_null' => 'Is Null',
            'is_not_null' => 'Is Not Null',
        ];
    }
}
