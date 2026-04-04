<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AllowedEmailDomain implements Rule
{
    public function passes($attribute, $value)
    {
        $allowedDomains = config('domains.allowed_email_domains', []);
        foreach ($allowedDomains as $domain) {
            if (str_ends_with($value, $domain)) {
                return true;
            }
        }
        return false;
    }

    public function message()
    {
        $domains = implode(', ', config('domains.allowed_email_domains', []));
        return "Разрешены только email адреса с доменами: {$domains}";
    }
}
