@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h2>Summary</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;">
        <x-stat-card label="Total posts" :value="$stats['total_posts']" />
        <x-stat-card label="Drafts" :value="$stats['draft_posts']" />
        <x-stat-card label="Published" :value="$stats['published_posts']" />
    </div>

    <x-panel title="Recent posts">
        <ul style="list-style:none;padding:0;">
            @forelse ($recentPosts as $post)
                <li style="padding:0.5rem 0;border-bottom:1px solid #eee;">
                    <a href="#">{{ $post->title }}</a>
                </li>
            @empty
                <li style="padding:0.5rem 0;">No posts yet.</li>
            @endforelse
        </ul>
    </x-panel>
@endsection
