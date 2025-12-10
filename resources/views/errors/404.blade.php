@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="max-w-lg w-full bg-white shadow-lg rounded-lg p-8 text-center">
        <h1 class="text-6xl font-bold text-yellow-500 mb-4">404</h1>
        <h2 class="text-2xl font-semibold mb-2">Page Not Found</h2>
        <p class="text-gray-600 mb-6">Sorry, the page you are looking for could not be found.<br>It might have been moved or deleted.</p>
        <div class="flex justify-center gap-4">
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go Home</a>
            <a href="mailto:support@example.com" class="btn btn-outline btn-secondary">Contact Support</a>
        </div>
    </div>
</div>
@endsection
