<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;

class DataSourceManager
{
    /**
     * Get available Eloquent models
     *
     * @return array
     */
    public function getAvailableModels(): array
    {
        $config = config('visual-report-builder');
        $namespace = $config['models']['namespace'] ?? 'App\\Models';
        $path = $config['models']['path'] ?? app_path('Models');

        if (!is_dir($path)) {
            return [];
        }

        $models = [];

        // Scan the models directory
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) {
                continue;
            }

            $className = substr($file, 0, -4);
            $modelClass = $namespace . '\\' . $className;

            try {
                if (class_exists($modelClass) && is_subclass_of($modelClass, Model::class)) {
                    $models[] = [
                        'class' => $modelClass,
                        'name' => $className,
                        'label' => $this->humanizeClassName($className),
                    ];
                }
            } catch (\Exception $e) {
                // Skip models that can't be loaded
                continue;
            }
        }

        return $models;
    }

    /**
     * Get model metadata (dimensions and metrics)
     *
     * @param string $modelClass
     * @return array
     */
    public function getModelMetadata(string $modelClass): array
    {
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
            return ['dimensions' => [], 'metrics' => []];
        }

        try {
            $instance = new $modelClass();

            $dimensions = $this->extractDimensions($instance);
            $metrics = $this->extractMetrics($instance);

            return [
                'dimensions' => $dimensions,
                'metrics' => $metrics,
            ];
        } catch (\Exception $e) {
            return ['dimensions' => [], 'metrics' => []];
        }
    }

    /**
     * Extract dimensions from model
     *
     * @param Model $model
     * @return array
     */
    protected function extractDimensions(Model $model): array
    {
        $dimensions = [];

        // Get table columns that are good for dimensions
        $columnNames = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());

        $excludeColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];

        foreach ($columnNames as $column) {
            if (!in_array($column, $excludeColumns) && !str_ends_with($column, '_id')) {
                $dimensions[] = [
                    'column' => $column,
                    'label' => $this->humanizeColumnName($column),
                    'type' => $this->guessColumnType($model, $column),
                ];
            }
        }

        // Also add relationship dimensions if model has Reportable trait
        if (method_exists($model, 'getReportableDimensions')) {
            $customDimensions = $model->getReportableDimensions();
            $dimensions = array_merge($dimensions, $customDimensions);
        }

        return $dimensions;
    }

    /**
     * Extract metrics from model
     *
     * @param Model $model
     * @return array
     */
    protected function extractMetrics(Model $model): array
    {
        $metrics = [];

        // Get numeric columns that are good for metrics
        $columnNames = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());

        $excludeColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];

        foreach ($columnNames as $column) {
            if (in_array($column, $excludeColumns)) {
                continue;
            }

            $type = $model->getConnection()->getSchemaBuilder()->getColumnType($model->getTable(), $column);

            // Include numeric and money columns
            if (in_array($type, ['integer', 'bigInteger', 'decimal', 'double', 'float', 'unsignedInteger', 'unsignedBigInteger'])) {
                $metrics[] = [
                    'column' => $column,
                    'label' => $this->humanizeColumnName($column),
                    'type' => $type,
                    'default_aggregate' => 'sum',
                ];
            }
        }

        // Also add relationship metrics if model has Reportable trait
        if (method_exists($model, 'getReportableMetrics')) {
            $customMetrics = $model->getReportableMetrics();
            $metrics = array_merge($metrics, $customMetrics);
        }

        return $metrics;
    }

    /**
     * Guess column type for display
     *
     * @param Model $model
     * @param string $column
     * @return string
     */
    protected function guessColumnType(Model $model, string $column): string
    {
        $type = $model->getConnection()->getSchemaBuilder()->getColumnType($model->getTable(), $column);

        return match ($type) {
            'date', 'datetime' => 'date',
            'boolean' => 'boolean',
            'integer', 'bigInteger', 'unsignedInteger', 'unsignedBigInteger' => 'integer',
            'decimal', 'double', 'float' => 'number',
            default => 'string',
        };
    }

    /**
     * Humanize column name
     *
     * @param string $column
     * @return string
     */
    protected function humanizeColumnName(string $column): string
    {
        return ucwords(str_replace(['_', '-'], ' ', Str::snake($column)));
    }

    /**
     * Humanize class name
     *
     * @param string $className
     * @return string
     */
    protected function humanizeClassName(string $className): string
    {
        return ucwords(str_replace(['_', '-'], ' ', Str::snake($className)));
    }

    /**
     * Get model relationships (BelongsTo, HasMany, HasOne, BelongsToMany)
     *
     * @param string $modelClass
     * @return array
     */
    public function getModelRelationships(string $modelClass): array
    {
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
            return [];
        }

        $relationships = [];

        try {
            $reflection = new ReflectionClass($modelClass);
            $instance = new $modelClass();

            // Get all public methods
            foreach ($reflection->getMethods() as $method) {
                // Skip private/protected methods and static methods
                if (!$method->isPublic() || $method->isStatic()) {
                    continue;
                }

                // Skip constructor and Laravel magic methods
                if (in_array($method->getName(), ['__construct', '__get', '__set', '__call', '__toString'])) {
                    continue;
                }

                try {
                    $result = $method->invoke($instance);

                    // Check if result is a relationship
                    if ($this->isRelation($result)) {
                        $relationType = get_class($result);
                        $relationName = $this->getRelationshipType($relationType);

                        if ($relationName) {
                            // Try to get the related model
                            $relatedModel = $this->getRelatedModel($result);

                            if ($relatedModel) {
                                $relationships[] = [
                                    'name' => $method->getName(),
                                    'type' => $relationName,
                                    'related_model' => get_class($relatedModel),
                                    'label' => $this->humanizeClassName($method->getName()),
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Skip methods that fail
                    continue;
                }
            }
        } catch (\Exception $e) {
            return [];
        }

        return $relationships;
    }

    /**
     * Check if result is a Relation
     *
     * @param mixed $result
     * @return bool
     */
    protected function isRelation($result): bool
    {
        $relationClasses = [
            'Illuminate\Database\Eloquent\Relations\BelongsTo',
            'Illuminate\Database\Eloquent\Relations\HasOne',
            'Illuminate\Database\Eloquent\Relations\HasMany',
            'Illuminate\Database\Eloquent\Relations\BelongsToMany',
            'Illuminate\Database\Eloquent\Relations\HasManyThrough',
            'Illuminate\Database\Eloquent\Relations\MorphOne',
            'Illuminate\Database\Eloquent\Relations\MorphMany',
        ];

        foreach ($relationClasses as $class) {
            if ($result instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get relationship type name
     *
     * @param string $relationClass
     * @return string|null
     */
    protected function getRelationshipType(string $relationClass): ?string
    {
        return match (class_basename($relationClass)) {
            'BelongsTo' => 'belongsTo',
            'HasOne' => 'hasOne',
            'HasMany' => 'hasMany',
            'BelongsToMany' => 'belongsToMany',
            'HasManyThrough' => 'hasManyThrough',
            'MorphOne' => 'morphOne',
            'MorphMany' => 'morphMany',
            default => null,
        };
    }

    /**
     * Get related model from relation
     *
     * @param mixed $relation
     * @return Model|null
     */
    protected function getRelatedModel($relation): ?Model
    {
        try {
            if (method_exists($relation, 'getRelated')) {
                return $relation->getRelated();
            }
        } catch (\Exception $e) {
            //
        }

        return null;
    }

    /**
     * Validate model class
     *
     * @param string $modelClass
     * @return bool
     */
    public function isValidModel(string $modelClass): bool
    {
        return class_exists($modelClass) && is_subclass_of($modelClass, Model::class);
    }
}
