<?php

/**
 * @var bool $reuired. Default true
 * @var string $name
 * @var string $label
 */

?>

<div class="form-group col-sm-12 @if ($required ?? true) required  @endif @error($name) text-danger @enderror @if(isset($wrapper_class)) {{ $wrapper_class }} @endif" >
    <label>{{ $label }}</label>
    <input type="text" name=" {{ $name }}" value="{{ old($name) }}" class="form-control @error($name) is-invalid @enderror">
    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
