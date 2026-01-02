@props([
    'id' => null,
    'name' => null,
    'value' => '',
    'placeholder' => 'قیمت را وارد کنید',
    'required' => false,
    'disabled' => false,
    'class' => 'form-control'
])

<div class="price-input-wrapper">
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">ریال</span>
        </div>
        <input 
            type="text" 
            class="price-display {{ $class }}" 
            id="{{ $id ? $id . '_display' : '' }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            autocomplete="off"
        >
    </div>
    <input 
        type="hidden" 
        class="price-base" 
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $required ? 'required' : '' }}
    >
</div>

<style>
.price-input-wrapper {
    position: relative;
}

.price-input-wrapper .input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-right: none;
    color: #495057;
    font-weight: 500;
}

.price-input-wrapper .price-display {
    border-left: none;
    direction: ltr;
    text-align: left;
}

.price-input-wrapper .price-display:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.price-input-wrapper .price-display:focus + .input-group-text {
    border-color: #80bdff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all price inputs on page load
    initializePriceInputs();
    
    // Function to initialize price inputs
    function initializePriceInputs() {
        const priceWrappers = document.querySelectorAll('.price-input-wrapper');
        
        priceWrappers.forEach(wrapper => {
            const displayInput = wrapper.querySelector('.price-display');
            const baseInput = wrapper.querySelector('.price-base');
            
            if (displayInput && baseInput) {
                // Set initial display value
                if (baseInput.value) {
                    displayInput.value = formatPrice(baseInput.value);
                }
                
                // Add event listeners
                displayInput.addEventListener('input', function(e) {
                    handlePriceInput(e, displayInput, baseInput);
                });
                
                displayInput.addEventListener('blur', function(e) {
                    handlePriceBlur(e, displayInput, baseInput);
                });
                
                displayInput.addEventListener('focus', function(e) {
                    handlePriceFocus(e, displayInput, baseInput);
                });
                
                displayInput.addEventListener('keydown', function(e) {
                    handlePriceKeydown(e, displayInput, baseInput);
                });
            }
        });
    }
    
    // Handle price input changes
    function handlePriceInput(e, displayInput, baseInput) {
        let value = e.target.value;
        
        // Remove all non-digit characters except decimal point
        value = value.replace(/[^\d.]/g, '');
        
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Update base input with raw value
        baseInput.value = value;
        
        // Format display value
        if (value) {
            const formattedValue = formatPrice(value);
            displayInput.value = formattedValue;
        }
    }
    
    // Handle price input blur
    function handlePriceBlur(e, displayInput, baseInput) {
        const value = baseInput.value;
        if (value) {
            displayInput.value = formatPrice(value);
        }
    }
    
    // Handle price input focus
    function handlePriceFocus(e, displayInput, baseInput) {
        const value = baseInput.value;
        if (value) {
            displayInput.value = value;
        }
    }
    
    // Handle price input keydown
    function handlePriceKeydown(e, displayInput, baseInput) {
        // Check if dot key is pressed
        if (e.key === '.') {
            e.preventDefault(); // Prevent default dot behavior
            
            const currentValue = baseInput.value || '0';
            const newValue = currentValue + '000';
            
            // Update base input
            baseInput.value = newValue;
            
            // Update display with formatting
            displayInput.value = formatPrice(newValue);
            
            // Trigger any custom events if needed
            displayInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
    
    // Format price with thousands separators
    function formatPrice(value) {
        if (!value) return '';
        
        // Convert to number and check if it's valid
        const num = parseFloat(value);
        if (isNaN(num)) return '';
        
        // Format with thousands separators
        return new Intl.NumberFormat('en-US').format(num);
    }
    
    // Global function to update price input value programmatically
    window.updatePriceInput = function(id, value) {
        const baseInput = document.getElementById(id);
        const displayInput = document.getElementById(id + '_display');
        
        if (baseInput && displayInput) {
            baseInput.value = value;
            displayInput.value = formatPrice(value);
        }
    };
    
    // Global function to get price input value
    window.getPriceInputValue = function(id) {
        const baseInput = document.getElementById(id);
        return baseInput ? baseInput.value : '';
    };
    
    // Global function to set price input value
    window.setPriceInputValue = function(id, value) {
        window.updatePriceInput(id, value);
    };
});
</script> 