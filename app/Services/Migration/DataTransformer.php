<?php

namespace App\Services\Migration;

class DataTransformer
{
    /**
     * Transform a source row to target MySQL representation.
     */
    public function transform(string $tableName, array $row): array
    {
        $transformed = [];

        foreach ($row as $column => $value) {
            // 1. Preserve NULL values
            if ($value === null) {
                $transformed[$column] = null;

                continue;
            }

            // 2. Preserve JSON data (measurements, downloaded_categories)
            if (in_array($column, ['measurements', 'downloaded_categories'])) {
                if (is_array($value)) {
                    $transformed[$column] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } elseif (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $transformed[$column] = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    } else {
                        $transformed[$column] = $value;
                    }
                } else {
                    $transformed[$column] = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                continue;
            }

            // 3. Preserve Boolean values
            if (is_bool($value) || in_array($column, ['is_active', 'cutting_slip_downloaded'])) {
                if (is_bool($value)) {
                    $transformed[$column] = $value ? 1 : 0;
                } elseif ($value === 'true' || $value === '1' || $value === 1 || $value === 't') {
                    $transformed[$column] = 1;
                } else {
                    $transformed[$column] = 0;
                }

                continue;
            }

            // 4. Preserve Timestamps & Dates
            if (in_array($column, ['created_at', 'updated_at', 'last_used_at', 'expires_at', 'started_at', 'completed_at'])) {
                if (is_string($value) && (str_contains($value, 'T') || str_contains($value, '+') || str_contains($value, 'Z'))) {
                    $transformed[$column] = date('Y-m-d H:i:s', strtotime($value));
                } else {
                    $transformed[$column] = $value;
                }

                continue;
            }

            if (in_array($column, ['order_date', 'delivery_date', 'delivered_date', 'entry_date', 'khoroch_date', 'payment_date'])) {
                if (is_string($value) && str_contains($value, 'T')) {
                    $transformed[$column] = date('Y-m-d', strtotime($value));
                } else {
                    $transformed[$column] = $value;
                }

                continue;
            }

            // 5. Preserve Numeric Precision
            if (in_array($column, ['total_spent', 'due_amount', 'total_amount', 'advance', 'due', 'unit_price', 'total_taka', 'khoroch', 'amount', 'price'])) {
                $transformed[$column] = number_format((float) $value, 2, '.', '');

                continue;
            }

            // 6. Preserve UUID casing
            if ($column === 'id' || str_ends_with($column, '_id')) {
                $transformed[$column] = strtolower(trim((string) $value));

                continue;
            }

            // 7. General String & Integer values
            if (is_string($value)) {
                $transformed[$column] = trim($value);
            } else {
                $transformed[$column] = $value;
            }
        }

        return $transformed;
    }
}
