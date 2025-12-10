@extends('layouts.app')

@section('title', 'Forbidden')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="max-w-lg w-full bg-white shadow-lg rounded-lg p-8 text-center">
        <h1 class="text-6xl font-bold text-purple-600 mb-4">403</h1>
        <h2 class="text-2xl font-semibold mb-2">Forbidden</h2>
        <p class="text-gray-600 mb-6">You do not have permission to access this page.<br>If you believe this is an error, please contact support.</p>
        <div class="flex justify-center gap-4">
            <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
            <a href="mailto:support@example.com" class="btn btn-outline btn-secondary">Contact Support</a>
        </div>
    </div>
</div>
@endsection
