@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Subscription Details</h1>
    <div class="mb-3">
        <strong>ID:</strong> {{ $subscription->id }}
    </div>
    <div class="mb-3">
        <strong>Is Admin:</strong> {{ $subscription->isadmin ? 'Yes' : 'No' }}
    </div>
    <div class="mb-3">
        <strong>Email:</strong> {{ $subscription->email }}
    </div>
    <div class="mb-3">
        <strong>Alert ID:</strong> {{ $subscription->alert_id }}
    </div>
    <div class="mb-3">
        <strong>Delivered:</strong> {{ $subscription->delivered ? 'Yes' : 'No' }}
    </div>
    <a href="{{ route('subscriptions.edit', $subscription) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
