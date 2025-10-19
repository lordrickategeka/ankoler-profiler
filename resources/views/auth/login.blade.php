<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="profiler">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-base-200">
    <div class="hero min-h-screen">
        <div class="hero-content flex-col lg:flex-row-reverse max-w-5xl w-full">

            <!-- Left side - Branding -->
            <div class="text-center lg:text-left lg:w-1/2">
                <div class="flex items-center justify-center lg:justify-start mb-8">
                    <div class="avatar">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-accent to-secondary flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-4xl font-bold text-base-content">Profiler</h1>
                        <p class="text-accent font-semibold">Performance Analytics</p>
                    </div>
                </div>
                <h2 class="text-3xl lg:text-5xl font-bold text-base-content mb-4">Welcome Back!</h2>
                <p class="text-lg text-base-content/70 mb-6">
                    Monitor, analyze, and optimize your application's performance with our comprehensive analytics
                    platform.
                </p>

                <!-- Feature highlights -->
                <div class="grid grid-cols-1 gap-4 max-w-md mx-auto lg:mx-0">
                    <div class="flex items-center space-x-3">
                        <div class="badge badge-accent badge-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-base-content/80">Real-time Performance Monitoring</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="badge badge-info badge-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <span class="text-base-content/80">Advanced Analytics Dashboard</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="badge badge-secondary badge-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-base-content/80">Enterprise Security</span>
                    </div>
                </div>
            </div>

            <!-- Right side - Login Form -->
            <div class="card card-lg w-full max-w-md shadow-2xl bg-base-100 lg:w-1/2">
                <div class="card-body">
                    <h3 class="card-title text-2xl font-bold text-center justify-center mb-6">Sign In</h3>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success mb-4">
                            <svg class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend sr-only">Login Credentials</legend>                            <!-- Email Address -->
                            <div class="form-control">
                                <label class="label" for="email">
                                    <span class="label-text font-medium">Email address</span>
                                </label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    autocomplete="email" required
                                    class="input input-bordered input-lg w-full @error('email') input-error @enderror"
                                    placeholder="Enter your email">
                                @error('email')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="form-control">
                                <label class="label" for="password">
                                    <span class="label-text font-medium">Password</span>
                                </label>
                                <input id="password" name="password" type="password" autocomplete="current-password"
                                    required class="input input-bordered input-lg w-full @error('password') input-error @enderror"
                                    placeholder="Enter your password">
                                @error('password')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="flex items-center justify-between mt-6">
                                <label class="label cursor-pointer">
                                    <input id="remember_me" name="remember" type="checkbox"
                                        class="checkbox checkbox-accent checkbox-sm">
                                    <span class="label-text ml-2">Remember me</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="link link-accent text-sm">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                        </fieldset>

                        <!-- Submit Button -->
                        <div class="form-control mt-6">
                            <button type="submit" id="loginBtn" class="btn btn-primary btn-lg w-full">
                                <!-- Loading Spinner (hidden by default) -->
                                <span id="loginSpinner" class="loading loading-spinner loading-sm mr-2 hidden"></span>
                                <!-- Login Icon -->
                                <svg id="loginIcon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                <span id="loginText">Sign In</span>
                            </button>
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="divider">Need help?</div>

                    <!-- Help Links -->
                    <div class="flex justify-center space-x-4 text-sm">
                        <a href="#" class="link link-accent">Contact Support</a>
                        <span class="text-base-content/40">|</span>
                        <a href="#" class="link link-accent">Documentation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer footer-center p-4 bg-base-200 text-base-content absolute bottom-0 w-full">
        <aside>
            <p>&copy; {{ date('Y') }} Profiler Analytics. All rights reserved.</p>
        </aside>
    </footer>

    <!-- Login Loading State Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('form');
            const loginBtn = document.getElementById('loginBtn');
            const loginSpinner = document.getElementById('loginSpinner');
            const loginIcon = document.getElementById('loginIcon');
            const loginText = document.getElementById('loginText');

            if (loginForm && loginBtn) {
                loginForm.addEventListener('submit', function() {
                    // Show loading state
                    loginSpinner.classList.remove('hidden');
                    loginIcon.classList.add('hidden');
                    loginText.textContent = 'Signing In...';
                    loginBtn.disabled = true;
                    loginBtn.classList.add('btn-disabled');
                });
            }
        });
    </script>
</body>

</html>
