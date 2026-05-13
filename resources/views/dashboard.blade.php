@extends('layouts.app')

@section('title', 'Dashboard — ' . config('app.name'))

@section('nav')
    <a href="{{ route('home') }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Home</a>
    <button type="button" id="logout-btn" class="rounded-md px-3 py-1.5 font-medium text-zinc-900 hover:bg-zinc-100 dark:text-zinc-100 dark:hover:bg-zinc-900">Log out</button>
@endsection

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Signed in with a JWT stored in this browser.</p>

        <div id="panel-loading" class="mt-8 rounded-xl border border-zinc-200 bg-white p-6 text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">Loading profile…</div>
        <div id="panel-guest" class="mt-8 hidden rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-100">
            No token found. <a href="{{ route('login') }}" class="font-medium underline">Log in</a>
        </div>
        <div id="panel-user" class="mt-8 hidden rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <dl class="space-y-2 text-sm">
                <div><dt class="text-zinc-500 dark:text-zinc-400">Name</dt><dd id="user-name" class="font-medium"></dd></div>
                <div><dt class="text-zinc-500 dark:text-zinc-400">Email</dt><dd id="user-email" class="font-medium"></dd></div>
                <div><dt class="text-zinc-500 dark:text-zinc-400">Google</dt><dd id="user-google" class="font-medium"></dd></div>
            </dl>
        </div>
    </div>
@endsection

@push('scripts')
@php
    $bootToken = session()->pull('access_token');
@endphp
<script>
(function () {
    const KEY = 'access_token';
    @if ($bootToken)
    localStorage.setItem(KEY, @json($bootToken));
    @endif

    const loading = document.getElementById('panel-loading');
    const guest = document.getElementById('panel-guest');
    const userPanel = document.getElementById('panel-user');

    async function loadMe() {
        const t = localStorage.getItem(KEY);
        if (!t) {
            loading.classList.add('hidden');
            guest.classList.remove('hidden');
            return;
        }
        const res = await fetch('{{ url('/auth/me') }}', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + t,
            },
            credentials: 'same-origin',
        });
        loading.classList.add('hidden');
        if (!res.ok) {
            localStorage.removeItem(KEY);
            guest.classList.remove('hidden');
            return;
        }
        const u = await res.json();
        document.getElementById('user-name').textContent = u.name || '—';
        document.getElementById('user-email').textContent = u.email || '—';
        document.getElementById('user-google').textContent = u.google_id ? 'Linked' : 'Not linked';
        userPanel.classList.remove('hidden');
    }

    document.getElementById('logout-btn').addEventListener('click', async () => {
        const t = localStorage.getItem(KEY);
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (t) {
            await fetch('{{ url('/auth/logout') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + t,
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            }).catch(() => {});
        }
        localStorage.removeItem(KEY);
        window.location = '{{ route('login') }}';
    });

    loadMe();
})();
</script>
@endpush
