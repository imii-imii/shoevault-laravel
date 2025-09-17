@extends('layouts.app')

@section('title', 'Reservations - ShoeVault Batangas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
@endpush

@section('content')
<div style="padding: 2rem; background: #f8f9fa; min-height: 100vh;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: #2d3748; margin: 0;">Customer Reservations</h1>
            <a href="{{ route('pos.dashboard') }}" style="background: #4a5568; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to POS
            </a>
        </div>
        
        <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 1rem;">Upcoming Reservations</h3>
            <p style="color: #718096;">Customer reservation management will be implemented here.</p>
            
            <div style="margin-top: 2rem; text-align: center; color: #a0aec0;">
                <i class="fas fa-calendar-alt" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>No reservations scheduled.</p>
            </div>
        </div>
    </div>
</div>
@endsection
