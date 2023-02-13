@extends('vendor/backpack/crud/create')
@section('content')
    @parent
@endsection

@section('after_scripts')
    @parent
    @include('admin.invoices.script')
@endsection

