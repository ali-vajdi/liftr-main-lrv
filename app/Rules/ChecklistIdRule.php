<?php

namespace App\Rules;

use App\Models\DescriptionChecklist;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ChecklistIdRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow 0 for custom checklists
        $checklistId = (int) $value;
        if ($checklistId === 0) {
            // Custom checklist - allow it
            return;
        }
        
        // For non-zero values, check if it exists in description_checklists
        if (!DescriptionChecklist::where('id', $checklistId)->exists()) {
            $fail('The selected checklist is invalid.');
        }
    }
}
