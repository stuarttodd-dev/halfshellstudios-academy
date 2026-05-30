@extends('layouts.app')

@section('title', 'Activity — '.config('app.name'))

@section('content')
    <h1>Sync activity</h1>
    <p class="muted">Queue summary for the offline-first model (sync jobs arrive in chapter 14).</p>

    <div class="card">
        <ul class="stats">
            <li><span>Pending</span><strong>{{ $pending }}</strong></li>
            <li><span>Synced</span><strong>{{ $synced }}</strong></li>
            <li><span>Failed</span><strong>{{ $failed }}</strong></li>
        </ul>
    </div>

    <h2 style="font-size:1rem;margin:1.25rem 0 0.5rem">Recent notes</h2>
    @forelse ($recent as $note)
        <div class="card">
            <p style="margin:0">{{ Str::limit($note->body, 120) }}</p>
            <p class="muted" style="margin:0.35rem 0 0">{{ $note->sync_state }} · {{ $note->updated_at->diffForHumans() }}</p>
        </div>
    @empty
        <p class="muted">Nothing to show yet.</p>
    @endforelse
@endsection
