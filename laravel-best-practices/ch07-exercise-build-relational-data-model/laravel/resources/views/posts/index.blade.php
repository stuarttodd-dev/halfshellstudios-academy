{{-- Minimal list: replace with your layout. --}}
<ul>
    @foreach ($posts as $post)
        <li>
            {{ $post->title }}
            — {{ $post->author?->name }}
            — tags: {{ $post->tags->pluck('name')->join(', ') }}
            — comments: {{ $post->comments_count }}
        </li>
    @endforeach
</ul>
{{ $posts->links() }}
