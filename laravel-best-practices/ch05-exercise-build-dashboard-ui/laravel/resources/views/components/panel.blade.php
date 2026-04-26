@props(['title' => null])
<div class="panel" style="border:1px solid #ddd;border-radius:8px;padding:1rem;margin-top:1rem;">
    @isset($title)
        <h2 style="margin-top:0;">{{ $title }}</h2>
    @endisset
    {{ $slot }}
</div>
