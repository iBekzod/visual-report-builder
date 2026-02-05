<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;

class DataSourceManager
{
    /**
     * Cache for column types to avoid repeated queries
     *
     * @var array
     */
    protected array $columnTypeCache = [];
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

        if ($files === false) {
            return [];
        }

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

            // Use our native type detection instead of Doctrine DBAL
            $type = $this->guessColumnType($model, $column);

            // Include numeric and money columns
            if (in_array($type, ['integer', 'number'])) {
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
     * Guess column type for display - uses native database introspection
     *
     * @param Model $model
     * @param string $column
     * @return string
     */
    protected function guessColumnType(Model $model, string $column): string
    {
        $table = $model->getTable();
        $cacheKey = $table . '.' . $column;

        if (isset($this->columnTypeCache[$cacheKey])) {
            return $this->columnTypeCache[$cacheKey];
        }

        $type = $this->getColumnTypeNative($model, $column);

        $this->columnTypeCache[$cacheKey] = $type;

        return $type;
    }

    /**
     * Get column type using native database introspection (no Doctrine DBAL)
     *
     * @param Model $model
     * @param string $column
     * @return string
     */
    protected function getColumnTypeNative(Model $model, string $column): string
    {
        try {
            $connection = $model->getConnection();
            $table = $model->getTable();
            $driver = $connection->getDriverName();

            // Try to get column info based on database driver
            $rawType = $this->getRawColumnType($connection, $table, $column, $driver);

            return $this->normalizeColumnType($rawType, $column);
        } catch (\Exception $e) {
            // Fallback: guess from column name
            return $this->guessTypeFromColumnName($column);
        }
    }

    /**
     * Get raw column type from database
     *
     * @param mixed $connection
     * @param string $table
     * @param string $column
     * @param string $driver
     * @return string
     */
    protected function getRawColumnType($connection, string $table, string $column, string $driver): string
    {
        try {
            switch ($driver) {
                case 'mysql':
                case 'mariadb':
                    $result = $connection->select(
                        "SELECT DATA_TYPE, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                        [$table, $column]
                    );
                    return $result[0]->DATA_TYPE ?? 'varchar';

                case 'pgsql':
                    $result = $connection->select(
                        "SELECT data_type, udt_name FROM information_schema.columns
                         WHERE table_name = ? AND column_name = ?",
                        [$table, $column]
                    );
                    return $result[0]->data_type ?? $result[0]->udt_name ?? 'varchar';

                case 'sqlite':
                    $result = $connection->select("PRAGMA table_info({$table})");
                    foreach ($result as $col) {
                        if ($col->name === $column) {
                            return strtolower($col->type);
                        }
                    }
                    return 'text';

                case 'sqlsrv':
                    $result = $connection->select(
                        "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                         WHERE TABLE_NAME = ? AND COLUMN_NAME = ?",
                        [$table, $column]
                    );
                    return $result[0]->DATA_TYPE ?? 'varchar';

                default:
                    return 'varchar';
            }
        } catch (\Exception $e) {
            return 'varchar';
        }
    }

    /**
     * Normalize database-specific type to generic type
     *
     * @param string $rawType
     * @param string $column
     * @return string
     */
    protected function normalizeColumnType(string $rawType, string $column): string
    {
        $rawType = strtolower(trim($rawType));

        // Date/time types
        if (in_array($rawType, ['date', 'datetime', 'timestamp', 'timestamptz', 'timestamp without time zone', 'timestamp with time zone', 'time', 'timetz'])) {
            return 'date';
        }

        // Boolean types
        if (in_array($rawType, ['boolean', 'bool', 'tinyint'])) {
            // tinyint(1) is often used as boolean in MySQL
            if ($rawType === 'tinyint' && (str_contains($column, 'is_') || str_contains($column, 'has_') || str_contains($column, 'can_'))) {
                return 'boolean';
            }
            if ($rawType !== 'tinyint') {
                return 'boolean';
            }
        }

        // Integer types
        if (in_array($rawType, ['integer', 'int', 'int2', 'int4', 'int8', 'smallint', 'mediumint', 'bigint', 'tinyint', 'serial', 'bigserial', 'smallserial'])) {
            return 'integer';
        }

        // Decimal/float types
        if (in_array($rawType, ['decimal', 'numeric', 'float', 'float4', 'float8', 'double', 'double precision', 'real', 'money'])) {
            return 'number';
        }

        // JSON types
        if (in_array($rawType, ['json', 'jsonb'])) {
            return 'json';
        }

        // Array types (PostgreSQL)
        if (str_starts_with($rawType, '_') || str_contains($rawType, '[]')) {
            return 'array';
        }

        // Default to string
        return 'string';
    }

    /**
     * Guess type from column name when database introspection fails
     *
     * @param string $column
     * @return string
     */
    protected function guessTypeFromColumnName(string $column): string
    {
        $column = strtolower($column);

        // Boolean patterns
        if (str_starts_with($column, 'is_') || str_starts_with($column, 'has_') || str_starts_with($column, 'can_') || str_starts_with($column, 'should_')) {
            return 'boolean';
        }

        // Date patterns
        if (str_contains($column, '_at') || str_contains($column, '_date') || str_contains($column, 'date_') || $column === 'date' || $column === 'birthday' || $column === 'dob') {
            return 'date';
        }

        // Numeric patterns
        if (str_contains($column, 'amount') || str_contains($column, 'price') || str_contains($column, 'cost') || str_contains($column, 'total') || str_contains($column, 'balance') || str_contains($column, 'rate') || str_contains($column, 'quantity') || str_contains($column, 'count') || str_contains($column, 'number') || str_contains($column, 'percent') || str_contains($column, 'score')) {
            return 'number';
        }

        // ID patterns (integer)
        if ($column === 'id' || str_ends_with($column, '_id')) {
            return 'integer';
        }

        return 'string';
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
