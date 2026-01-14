<?php

namespace Ibekzod\VisualReportBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataSource extends Model
{
    protected $table = 'visual_data_sources';

    protected $fillable = [
        'name',
        'description',
        'type',
        'model_class',
        'configuration',
        'user_id',
        'is_public',
    ];

    protected $casts = [
        'configuration' => 'json',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the data source
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        return $this->belongsTo($userModel);
    }

    /**
     * Scope to get public data sources
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to get data sources owned by user
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get available data sources (owned or public)
     */
    public function scopeAvailable($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_public', true);
        });
    }

    /**
     * Get model metadata
     */
    public function getMetadata(): array
    {
        $builder = app('visual-report-builder');

        if ($this->type === 'eloquent' && class_exists($this->model_class)) {
            return $builder->getMetadata($this->model_class);
        }

        return [];
    }

    /**
     * Create data source from model
     */
    public static function fromModel(string $model, $userId, $isPublic = false): self
    {
        return self::create([
            'name' => class_basename($model),
            'type' => 'eloquent',
            'model_class' => $model,
            'user_id' => $userId,
            'is_public' => $isPublic,
        ]);
    }

    /**
     * Get available types
     */
    public static function getAvailableTypes(): array
    {
        return ['eloquent', 'database', 'api', 'csv'];
    }

    /**
     * Test connection
     */
    public function testConnection(): bool
    {
        try {
            if ($this->type === 'eloquent' && class_exists($this->model_class)) {
                $model = new $this->model_class();
                // Try to get first record
                $model::first();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get sample data
     */
    public function getSampleData($limit = 5): array
    {
        if ($this->type === 'eloquent' && class_exists($this->model_class)) {
            $model = $this->model_class;
            return $model::limit($limit)->get()->toArray();
        }

        return [];
    }
}
