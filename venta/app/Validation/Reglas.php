<?php
namespace App\Validation;

class Reglas
{
    public function mayorDeCero(string $str): bool
    {
        $a = round(floatval($str), 2);
        if($a <= 0) {
            return false;
        }
        return true;
    }
    public function mayorIgualCero(string $str): bool
    {
        $a = round(floatval($str), 2);
        if($a < 0) {
            return false;
        }
        return true;
    }
}