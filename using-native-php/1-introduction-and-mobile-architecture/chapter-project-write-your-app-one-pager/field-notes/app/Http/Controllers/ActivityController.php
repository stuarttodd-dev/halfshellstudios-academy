<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OfflineNote;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        $pending = OfflineNote::query()->where('sync_state', OfflineNote::SYNC_PENDING)->count();
        $synced = OfflineNote::query()->where('sync_state', OfflineNote::SYNC_SYNCED)->count();
        $failed = OfflineNote::query()->where('sync_state', OfflineNote::SYNC_FAILED)->count();

        return view('activity.index', [
            'pending' => $pending,
            'synced' => $synced,
            'failed' => $failed,
            'recent' => OfflineNote::query()->latest()->limit(10)->get(),
        ]);
    }
}
