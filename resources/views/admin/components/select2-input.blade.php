@props([
    'id' => null,
    'name' => null,
    'class' => 'form-control',
    'placeholder' => 'انتخاب کنید',
    'options' => [],
    'selected' => null,
    'required' => false,
    'disabled' => false,
    'tags' => false,
    'dir' => 'rtl',
    'dropdownParent' => null,
    'dropdownAutoWidth' => true
])

<select 
    class="select2-input {{ $class }}" 
    id="{{ $id }}"
    name="{{ $name }}"
    {{ $required ? 'required' : '' }}
    {{ $disabled ? 'disabled' : '' }}
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    
    @foreach($options as $option)
        @if(is_array($option))
            <option value="{{ $option['value'] }}" {{ $selected == $option['value'] ? 'selected' : '' }}>
                {{ $option['label'] }}
            </option>
        @else
            <option value="{{ $option }}" {{ $selected == $option ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endif
    @endforeach
</select> 