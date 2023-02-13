@extends('vendor/backpack/crud/edit')
@section('content')
    @parent
@endsection

@section('after_scripts')
    @parent
    @include('admin.invoices.script')
@endsection

