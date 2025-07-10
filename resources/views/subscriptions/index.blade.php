@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Subscriptions</h1>
    @if(!session()->has('customer_id'))
    <a href="{{ route('subscriptions.create') }}" class="btn btn-primary mb-3">Add Subscription</a>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Trip</th>
                <th>Type</th>
                <th>Coordinates</th>
                <th>Delivered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subscriptions as $subscription)
                <tr>
                    <td>{{ $subscription->id }}</td>
                    <td>{{ $subscription->email }}</td>
                    <td>{{ $subscription->alert ? $subscription->alert->message : '-' }}</td>
                    <td>{{ $subscription->alert ? $subscription->alert->type : '-' }}</td>
                    <td>{{ $subscription->alert ? $subscription->alert->latitude . ', ' . $subscription->alert->longitude : '-' }}</td>
                    <td>{{ $subscription->delivered ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-info btn-sm">View</a>
                        <form action="{{ route('subscriptions.destroy', $subscription) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
