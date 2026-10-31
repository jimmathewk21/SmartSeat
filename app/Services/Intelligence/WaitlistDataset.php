<?php

namespace App\Services\Intelligence;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;

class WaitlistDataset
{
    public function __construct(private WaitlistHistory $history) {}

    public function split(CarbonImmutable $asOf): array
    {
        $rows = $this->history->rows($asOf);
        if (count($rows) < 2) {
            return ['ready' => false, 'eligible' => count($rows), 'train' => [], 'validation' => [], 'purged' => 0, 'boundary' => null];
        }
        $boundary = $rows[(int) floor(count($rows) * 0.8)]['observed_at'];
        $train = $validation = [];
        $purged = 0;
        foreach ($rows as $row) {
            if ($row['observed_at'] >= $boundary) {
                $validation[] = $row;
            } elseif ($row['mature_at'] <= $boundary) {
                $train[] = $row;
            } else {
                $purged++; // Outcome was unknown when validation prediction would occur.
            }
        }
        $ready = count($train) >= Config::get('smartseat.intelligence.min_training_rows')
            && count($validation) >= Config::get('smartseat.intelligence.min_validation_rows');
        foreach ([$train, $validation] as $partition) {
            foreach ([0, 1] as $label) {
                $ready = $ready && count(array_filter($partition, fn ($row) => $row['label'] === $label)) >= Config::get('smartseat.intelligence.min_class_rows');
            }
        }

        return ['ready' => $ready, 'eligible' => count($rows), 'train' => $train, 'validation' => $validation,
            'purged' => $purged, 'boundary' => $boundary];
    }
}
