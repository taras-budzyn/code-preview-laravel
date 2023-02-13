@extends('vendor/backpack/crud/create')
@section('content')
    @parent
    <div id="saveActions" class="form-group">
        <input type="hidden" name="save_action" value="save_and_back">
        <button id="generateInvoices" type="submit" class="btn btn-success">
            <span class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
            <span data-value="save_and_back">@lang('Generate')</span>
        </button>

        <div class="btn-group" role="group">
                </div>
        <a href="{{ backpack_url('invoice') }}" class="btn btn-default"><span class="la la-ban"></span> &nbsp;@lang('Cancel')</a>
    </div>
@endsection

@section('after_scripts')
    @parent
    @include('admin.invoices.generateScript')
@endsection

