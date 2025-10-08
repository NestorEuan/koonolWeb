<?php

namespace App\Controllers\Catalogos;

use App\Controllers\BaseController;

class Prueba extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function ver($par1, $par2 = 'solo'){
         echo ('Par1: ' . $par1 . '<BR>' . 'Par2: ' . $par2); 
    }
}
