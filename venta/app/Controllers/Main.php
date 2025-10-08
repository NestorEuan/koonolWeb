<?php

namespace App\Controllers;
// use App\Models\Seguridad\MenuMdl;


class Main extends BaseController
{

    public function index()
    {
        $r = $this->validaSesion(true);
        if($r !== false) return $r;
        echo view('templates/header', $this->dataMenu);
        echo view('desktop', $this->dataMenu);
        echo view('templates/footer', $this->dataMenu);
    }
}

?>
