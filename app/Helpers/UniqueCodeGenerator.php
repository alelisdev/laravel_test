<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class UniqueCodeGenerator
{
    /**
     * Generate a unique code of 15 characters.
     *
     * @return string
     */
    public static function generateUniqueCode()
    {
        return Str::random(15);
    }
}
