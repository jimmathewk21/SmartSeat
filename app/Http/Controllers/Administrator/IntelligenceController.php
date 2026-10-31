<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Requests\Administrator\TrainWaitlistModelRequest;
use App\Services\Intelligence\WaitlistDataset;
use App\Services\Intelligence\WaitlistModels;
use App\Services\Intelligence\WaitlistPrediction;
use App\Services\Intelligence\WaitlistTraining;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class IntelligenceController extends CatalogueController
{
    public function index(WaitlistDataset $dataset, WaitlistModels $models): View
    {
        $report = $dataset->split(CarbonImmutable::now('UTC'));
        $model = $models->load();

        return view('administrator.intelligence', ['report' => $report, 'model' => $model, 'unavailable' => WaitlistPrediction::UNAVAILABLE]);
    }

    public function train(TrainWaitlistModelRequest $request, WaitlistTraining $training): RedirectResponse
    {
        $lock = Cache::lock('smartseat-waitlist-training', 600);
        if (! $lock->get()) {
            throw ValidationException::withMessages(['training' => 'Another training operation is in progress.']);
        }
        try {
            $result = $training->train();
        } finally {
            $lock->release();
        }

        return redirect()->route('administrator.intelligence.index')->with('status', $result['message']);
    }
}
