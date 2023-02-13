@if ($entry->canEdit())
    <a href="{{ route('invoice.edit', ['id' => $entry->getKey()]) }}"   class="btn btn-sm btn-link">
        <i class="las la-edit"></i> @lang('Edit')
    </a>
@endif