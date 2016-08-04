<?php

namespace App\Traits;

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

        return $number->getNationalNumber();
    }
}
