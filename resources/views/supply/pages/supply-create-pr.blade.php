{{-- Extend the main layout that you want to use --}}
@extends('layouts.supply-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Create Purchase Request | I-TRAC')

@section('content')
    @include('general-pages.create-pr')
@endsection
