@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Financial Year</h1>
    <form method="POST" action="{{ route('accounting.financial-years.update', $financialYear) }}">
        @csrf
        @method('PUT')
        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ $financialYear->name }}" required>
        </div>
        <div>
            <label for="code">Code</label>
            <input type="text" id="code" name="code" value="{{ $financialYear->code }}" required>
        </div>
        <div>
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="{{ $financialYear->start_date->format('Y-m-d') }}" required>
        </div>
        <div>
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" value="{{ $financialYear->end_date->format('Y-m-d') }}" required>
        </div>
        <div>
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes">{{ $financialYear->notes }}</textarea>
        </div>
        <button type="submit">Update Financial Year</button>
    </form>
</div>
@endsection