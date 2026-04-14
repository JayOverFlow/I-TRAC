{{-- Extend the main layout that you want to use --}}
@extends('layouts.head-layout')

{{-- Define contents to show in the layout --}}
@section('title', 'Tasks | I-TRAC')

@include('general-pages.tasks')
