@extends('layouts.app')

@section('title', 'Settings — Field Notes')
@section('top_title', 'Settings')
@section('top_subtitle', 'Field Notes')

@section('content')
    <div class="mx-auto max-w-md px-5 pt-4">
        <ul class="divide-y divide-slate-800 rounded-xl border border-slate-700/80 bg-slate-800/40">
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">App</span>
                <span class="text-sm text-slate-500">Field Notes</span>
            </li>
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">Bundle ID</span>
                <span class="text-xs text-slate-500">{{ config('nativephp.app_id') ?: env('NATIVEPHP_APP_ID', '—') }}</span>
            </li>
            <li class="flex items-center justify-between px-4 py-3">
                <span class="text-sm text-slate-300">Biometric lock</span>
                <span class="text-sm text-slate-500">Chapter 7</span>
            </li>
        </ul>
    </div>
@endsection
