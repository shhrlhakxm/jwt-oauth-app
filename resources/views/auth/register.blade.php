@extends('layouts.app')

@section('title', 'Sign up — ' . config('app.name'))

@section('nav')
    <a href="{{ route('home') }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Home</a>
    <a href="{{ route('login') }}" class="rounded-md px-3 py-1.5 font-medium text-zinc-900 hover:bg-zinc-100 dark:text-zinc-100 dark:hover:bg-zinc-900">Log in</a>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-md">
        <h1 class="text-2xl font-semibold tracking-tight">Create account</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Name, email, and password (min. 8 characters).</p>

        <form id="register-form" class="mt-8 space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
                <input type="text" id="name" name="name" required autocomplete="name" class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email" class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password</label>
                <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900">
            </div>
            <p id="register-error" class="hidden text-sm text-red-600 dark:text-red-400" role="alert"></p>
            <button type="submit" class="w-full rounded-lg bg-zinc-900 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">Sign up</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('register-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const err = document.getElementById('register-error');
    err.classList.add('hidden');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('{{ url('/auth/register') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
        }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const msg = data.message || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Could not register.';
        err.textContent = msg;
        err.classList.remove('hidden');
        return;
    }
    if (data.access_token) {
        localStorage.setItem('access_token', data.access_token);
        window.location = '{{ route('dashboard') }}';
    }
});
</script>
@endpush
