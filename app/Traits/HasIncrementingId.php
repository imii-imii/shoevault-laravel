<?php

namespace App\Traits;

trait HasIncrementingId
{
    /**
     * Boot the trait.
     */
    protected static function bootHasIncrementingId()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = static::generateIncrementingId($model);
            }
        });
    }

    /**
     * Generate an incrementing ID for the model.
     */
    protected static function generateIncrementingId($model)
    {
        $prefix = $model->getIdPrefix();
        
        // Get the highest existing ID number for this prefix
        $lastRecord = static::where($model->getKeyName(), 'LIKE', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING({$model->getKeyName()}, " . (strlen($prefix) + 1) . ") AS UNSIGNED) DESC")
            ->first();
        
        if ($lastRecord) {
            // Extract the number part and increment
            $lastId = $lastRecord->{$model->getKeyName()};
            $numberPart = substr($lastId, strlen($prefix));
            $nextNumber = intval($numberPart) + 1;
        } else {
            // Start from 1 if no records exist
            $nextNumber = 1;
        }
        
        // Format with leading zeros to maintain consistent length
        $paddedNumber = str_pad($nextNumber, $model->getIdNumberLength(), '0', STR_PAD_LEFT);
        
        return $prefix . $paddedNumber;
    }

    /**
     * Get the prefix for the auto-generated ID.
     * Override this method in your model.
     */
    protected function getIdPrefix(): string
    {
        return 'ID';
    }

    /**
     * Get the length of the number part (excluding prefix).
     * Override this method in your model.
     */
    protected function getIdNumberLength(): int
    {
        return 6; // Default: 6 digits (e.g., USR000001)
    }

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Get the data type of the primary key ID.
     */
    public function getKeyType()
    {
        return 'string';
    }
}