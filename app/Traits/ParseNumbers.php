<?php

namespace App\Traits;


use Log;

trait ParseNumbers
{

    /**
     * Parses the phonenuber for the title
     * @param  String $value The phone number
     * @return String        The formated number
     */
    public function parseNumber($number)
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

        try {
            $number = $phoneUtil->parse($number, "GB");

        } catch (\libphonenumber\NumberParseException $e) {

            Log::info("Failed Phone Number Parse");
        }

        // Check if is object and has been handled by parseNumber
        if( is_object($number) ) return $number->getNationalNumber();

        return null;
    }
}
