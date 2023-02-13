<?php
/**
* @var bool $reuired. Default true
* @var string $name
* @var string $label
* @var string $id
* @var string $wrapper_class
*/

?>

<div class="form-group col-sm-12 @if ($required ?? true) required @endif @error($name) text-danger @enderror @if(isset($wrapper_class)) {{ $wrapper_class }} @endif" >
    <div class="checkbox">
        <input type="checkbox" id="{{ $name }}_checkbox" name="{{ $name }}" @if(old($name)) checked @endif>
        <label class="form-check-label font-weight-normal" for="{{ $name }}_checkbox">{{ $label }}</label>
    </div>
    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
