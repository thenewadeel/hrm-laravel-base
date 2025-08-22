<?php
// app/Services/SequenceService.php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SequenceService
{
    public function generate(string $sequenceName, array $options = []): string
    {
        return DB::transaction(function () use ($sequenceName, $options) {
            $sequence = DB::table('sequences')
                ->where('name', $sequenceName)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                throw new \InvalidArgumentException("Sequence '{$sequenceName}' not found");
            }

            $nextValue = $sequence->last_value + $sequence->increment_by;

            // Update immediately - we're in a transaction
            DB::table('sequences')
                ->where('name', $sequenceName)
                ->update(['last_value' => $nextValue]);

            return $this->formatSequence($sequence, $nextValue, $options);
        });
    }

    // NEW: Reserve a sequence number without committing
    public function reserve(string $sequenceName, array $options = []): array
    {
        return DB::transaction(function () use ($sequenceName, $options) {
            $sequence = DB::table('sequences')
                ->where('name', $sequenceName)
                ->lockForUpdate()
                ->first();

            $nextValue = $sequence->last_value + $sequence->increment_by;
            $formattedCode = $this->formatSequence($sequence, $nextValue, $options);

            // Return the code but DON'T update the sequence yet
            return [
                'value' => $nextValue,
                'formatted' => $formattedCode,
                'sequence_data' => (array) $sequence
            ];
        });
    }

    // NEW: Commit a reserved sequence number
    public function commitReservation(string $sequenceName, int $reservedValue): void
    {
        DB::table('sequences')
            ->where('name', $sequenceName)
            ->where('last_value', '<', $reservedValue) // Prevent overwriting newer values
            ->update(['last_value' => $reservedValue]);
    }

    // NEW: Get current value without incrementing
    public function peek(string $sequenceName): int
    {
        $sequence = DB::table('sequences')
            ->where('name', $sequenceName)
            ->first();

        return $sequence ? $sequence->last_value : 0;
    }

    protected function formatSequence(object $sequence, int $value, array $options): string
    {
        $prefix = $options['prefix'] ?? $sequence->prefix;
        $suffix = $options['suffix'] ?? $sequence->suffix;
        $padLength = $options['pad_length'] ?? $sequence->pad_length;

        $number = str_pad($value, $padLength, '0', STR_PAD_LEFT);

        return $prefix . $number . $suffix;
    }
}
