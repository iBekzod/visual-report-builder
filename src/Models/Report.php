<?php

namespace Ibekzod\VisualReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $table = 'visual_reports';

    protected $fillable = [
        'name',
        'description',
        'model',
        'configuration',
        'view_options',
        'user_id',
    ];

    protected $casts = [
        'configuration' => 'json',
        'view_options' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created this report
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        return $this->belongsTo($userModel);
    }

    /**
     * Get the shares for this report
     */
    public function shares(): HasMany
    {
        return $this->hasMany(ReportShare::class);
    }

    /**
     * Share this report with a user
     */
    public function shareWith(int $userId, bool $canEdit = false, bool $canShare = false): ReportShare
    {
        return $this->shares()->updateOrCreate(
            ['user_id' => $userId],
            ['can_edit' => $canEdit, 'can_share' => $canShare]
        );
    }

    /**
     * Stop sharing this report with a user
     */
    public function stopSharingWith(int $userId): bool
    {
        return $this->shares()
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * Execute the report and return results
     */
    public function execute(): array
    {
        $modelClass = $this->model;

        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} does not exist");
        }

        // Get the model instance
        $model = new $modelClass();

        // Build the query based on configuration
        $query = $modelClass::query();

        // Apply any filters from configuration
        if (isset($this->configuration['filters']) && is_array($this->configuration['filters'])) {
            foreach ($this->configuration['filters'] as $filter) {
                if (isset($filter['column']) && isset($filter['operator']) && isset($filter['value'])) {
                    switch ($filter['operator']) {
                        case '=':
                            $query->where($filter['column'], $filter['value']);
                            break;
                        case '!=':
                            $query->where($filter['column'], '!=', $filter['value']);
                            break;
                        case '>':
                            $query->where($filter['column'], '>', $filter['value']);
                            break;
                        case '<':
                            $query->where($filter['column'], '<', $filter['value']);
                            break;
                        case '>=':
                            $query->where($filter['column'], '>=', $filter['value']);
                            break;
                        case '<=':
                            $query->where($filter['column'], '<=', $filter['value']);
                            break;
                        case 'like':
                            $query->where($filter['column'], 'like', "%{$filter['value']}%");
                            break;
                        case 'in':
                            $query->whereIn($filter['column'], (array)$filter['value']);
                            break;
                        case 'between':
                            $query->whereBetween($filter['column'], (array)$filter['value']);
                            break;
                    }
                }
            }
        }

        // Execute query and return results
        return [
            'columns' => $model->getFillable(),
            'data' => $query->get()->toArray(),
            'count' => $query->count(),
        ];
    }
}
