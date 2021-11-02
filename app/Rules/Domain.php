<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Domain implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/(?=^.{4,253}$)(^(?!-)([a-zA-Z0-9\-]{0,62}[a-zA-Z0-9]\.)*[a-zA-Z]{1,63}$)/',
                $value) ||
            !preg_match('/(?=^.{3,63}$)(^(?!-)([a-zA-Z0-9\-]{1,62}[a-zA-Z0-9]{1,62}$))/',
                $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid domain or hostname without an http protocol e.g. google.com, www.google.com';
    }
}
