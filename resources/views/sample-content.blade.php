{{-- Extend the main layout that you want to use --}}
@extends('layouts.sample-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Sample Content')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sample.css') }}"> {{-- Used asset() helper taht is point to the /public  dir --}}

@section('content')
    <h1 class="header-text">This is a sample content</h1>
    <p id="number">0</p>
    <button id="increment">+</button>

@section('js')
<script src="{{ asset('js/sample.js') }}" ></script>
@endsection