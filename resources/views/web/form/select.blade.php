<?php

/**
 * @var bool $reuired. Default true
 * @var string $name
 * @var string $label
 * @var string $id
 * @var string $wrapper_class
 */

?>

<div class="form-group col-sm-12 @if ($required ?? true) required  @endif @error($name) text-danger @enderror @if(isset($wrapper_class)) {{ $wrapper_class }} @endif" >
    <label>{{ $label }}</label>
    <select name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" @if(isset($id)) id="{{ $id }}" @endif>
    @if ($null ?? false)
        <option value="">-</option>
    @endif
    @foreach($options as $key => $value)
        <option @if (old($name) == $key) selected @endif value="{{ $key }}">{{ $value  }}</option>
    @endforeach
    </select>
    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
