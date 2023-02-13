@extends('vendor/backpack/crud/show')

@php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];

// if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
@parent
@endsection

@section('content')
@parent

<div class="row">
	<div class="col-md-12">
		<div class="">
			<h2>@lang('Invoice line items')</h2>
			@if(backpack_user()->can('modify contracts') && $invoice->status != App\Models\Invoice::STATUS_SENT)
			<a href="{{ route('invoice.new-line-item', ['invoice' => $invoice->id]) }}" class="btn btn-info" data-style="zoom-in">
				<span class="ladda-label"><i class="la la-users"></i>@lang("New Line Item")</span>
			</a>
			@endif
			<div class="card no-padding no-border">
				<table class="table table-striped mb-0">
					<tr>
						<th>@lang('Line Item type')</th>
						<th>@lang('Line item custom text')</th>

						<th>@lang('Amount in units')</th>

						<th>@lang('Price per unit')</th>
						<th>@lang('Total')</th>
						<th>@lang('Invoice line items status')</th>

						@if(backpack_user()->can('update invoice'))
						<th>@lang('Actions')</th>
						@endif
					</tr>
					<tbody>
						@if(!$invoice->lineItems->isEmpty())
						@foreach($invoice->lineItems as $lineItem)
						<tr data-id="{{ $lineItem->id }}">
							<td>
								{{__(sprintf('invoiceItem.%s', $lineItem->type))}}
							</td>
							<td>
								{{$lineItem->text}}
							</td>
							<td>
								{{$lineItem->amount}}
							</td>
							<td>
								{{$lineItem->price_per_unit}}
							</td>
							<td>
								{{$lineItem->amount * $lineItem->price_per_unit}}
							</td>
							<td>
								{{__(sprintf('invoiceItem.%s', $lineItem->status))}}
							</td>

							@if(backpack_user()->can('update invoice'))
							<td>
								@if($invoice->status != App\Models\Invoice::STATUS_SENT && $invoice->status != App\Models\Invoice::STATUS_PAID)
								<a href="{{ route('invoice.edit-line-item', ['invoice' => $invoice->id, 'invoiceItem' => $lineItem->id]) }}" class="btn btn-sm btn-link" data-button-type="import"><span class="ladda-label">
										<i class="la la-edit"></i> {{ __('Edit') }} </span></a>

								<a href="javascript:void(0)" onclick="confirmDelete(this)" data-route="{{ route('invoice.delete-line-item', ['invoice' => $invoice->id, 'invoiceItem' => $lineItem->id]) }}" data-title="{{ __('Delete')}}" data-text="{{ __('Are you sure you want to remove this line item?')}}" class="btn btn-sm btn-link" data-button-type="confirmDelete">
									<span class="ladda-label"><i class="la la-times"></i> @lang('Delete')</span>
								</a>
								@endif
							</td>
							@endif
						</tr>
						@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection


@section('after_styles')
@parent
@endsection

@section('after_scripts')
@parent
@endsection


@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
	function confirmDelete(button) {
		var button = $(button);
		var route = button.attr('data-route');
		var title = button.attr('data-title');
		var text = button.attr('data-text');

		swal({
			title: title,
			text: text,
			icon: "warning",
			buttons: {
				cancel: {
					text: "@lang('Cancel')",
					value: null,
					visible: true,
					className: "bg-secondary",
					closeModal: true,
				},
				delete: {
					text: "@lang('Delete')",
					value: true,
					visible: true,
					className: "bg-danger",
				}
			},
		}).then((value) => {
			if (value) {
				window.location.href = route;
			}
		})
	}
</script>
@if (!request()->ajax()) @endpush @endif