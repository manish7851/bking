@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Subscription</h1>
    <form action="{{ route('subscriptions.update', $subscription) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="isadmin" class="form-label">Is Admin</label>
            <select name="isadmin" id="isadmin" class="form-control">
                <option value="0" {{ !$subscription->isadmin ? 'selected' : '' }}>No</option>
                <option value="1" {{ $subscription->isadmin ? 'selected' : '' }}>Yes</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $subscription->email }}" required>
        </div>
        <div class="mb-3">
            <label for="alert_id" class="form-label">Alert</label>
            <select name="alert_id" id="alert_id" class="form-control">
                @foreach($alerts as $alert)
                    <option value="{{ $alert->id }}" {{ $subscription->alert_id == $alert->id ? 'selected' : '' }}>{{ $alert->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="delivered" class="form-label">Delivered</label>
            <select name="delivered" id="delivered" class="form-control">
                <option value="0" {{ !$subscription->delivered ? 'selected' : '' }}>No</option>
                <option value="1" {{ $subscription->delivered ? 'selected' : '' }}>Yes</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
