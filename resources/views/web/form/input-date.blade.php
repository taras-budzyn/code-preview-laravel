<?php

/**
 * @var bool $reuired. Default true
 */

$required = $reuired ?? true;

?>

<div class="form-group col-sm-12 required @error($name) text-danger @enderror" >
    <label>{{ $label }}</label>
    <input type="date" required pattern="\d{4}-\d{2}-\d{2}" name=" {{ $name }}" value="{{ old($name) }}" class="form-control @error($name) is-invalid @enderror">
    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
