@extends('layouts.app')

@section('title', 'Analytics Dashboard - ShoeVault Batangas')

@section('content')
<div style="display: flex; align-items: center; justify-content: center; height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center;">
    <div>
        <i class="fas fa-chart-line" style="font-size: 4rem; margin-bottom: 2rem; opacity: 0.8;"></i>
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Analytics Dashboard</h1>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem;">Coming Soon</p>
        <p style="opacity: 0.7;">This feature will be available for business owners to view comprehensive analytics and reports.</p>
        
        <div style="margin-top: 3rem;">
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3); color: white; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 1rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
