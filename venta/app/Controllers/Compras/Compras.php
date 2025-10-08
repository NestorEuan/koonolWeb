<?php

namespace App\Controllers\Compras;

use App\Controllers\BaseController;
use App\Models\Compras\ComprasMdl;
use App\Models\Compras\CompraDetalleMdl;

class Compras extends BaseController
{

    public function index()
    {
        $session = session();

        $data = [];
        $model = new ComprasMdl();

        $data['registros'] =  $model
            ->join('cpproveedor p', 'p.nIdProveedor = cpcompra.nIdProveedor')
            ->join('alsucursal a', 'a.nIdSucursal = cpcompra.nIdSucursal')
            ->paginate(5, 'compras');
        $data['comprapager'] = $model->pager;

        helper(['cookie']);
        $data['navbar'] = get_cookie('mainMenu');
        echo view('templates/header', $data);
        echo view('compras/compras', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function ver($id = 0)
    {
        helper(['form', 'url']);
        $validation = \Config\Services::validation();

        $session = session();
        $compra = new ComprasMdl();
        $compradet = new CompraDetalleMdl();

        $this->validate([]);
        $comprareg = $compra
            ->join('cpproveedor p', 'p.nIdProveedor = cpcompra.nIdProveedor')
            ->join('alsucursal a', 'a.nIdSucursal = cpcompra.nIdSucursal')
            ->where('cpcompra.nIdCompra', intval($id))
            ->first();
        //->getRegistros($id);

        //getRegistros($id);
        $arts = $compradet
            ->join('alarticulo a', 'a.nIdArticulo = cpcompradetalle.nIdArticulo')
            ->where('cpcompradetalle.nIdCompra', intval($id))
            ->findAll();
        //->getRegistrosByField('nIdCompra',$id);

        $_SESSION['lstCompraArt'] = array();
        if (!empty($arts)) {

            foreach ($arts as $artic) {

                $_SESSION['lstCompraArt'][] = array(
                    $artic['nIdArticulo'],
                    $artic['sDescripcion'],
                    $artic['fImporte'],
                    $artic['fCantidad'],
                );
            }
        }

        $timestamp = strtotime($comprareg['dcompra']);
        $comprareg['dcompra'] = date("Y-m-d", $timestamp);

        $data = [
            'registros' => $_SESSION['lstCompraArt'] ?? [],
            'enfoque' => $_SESSION['foc'] ?? 'art',
            'registro' => $comprareg,
        ];
        //$data['validation'] = $this->validator;

        helper(['cookie']);
        $data['navbar'] = get_cookie('mainMenu');
        echo view('templates/header', $data);
        echo view('compras/compramtto', $data);
        echo view('templates/footer', $this->dataMenu);
    }

    public function registrar()
    {
        helper(['form', 'url']);
        $session = session();

        $data = [
            'registros' => $_SESSION['lstCompraArt'] ?? [],
            'enfoque' => $_SESSION['foc'] ?? 'art'
        ];

        $validation = \Config\Services::validation();

        $model = new ComprasMdl();
        $detalle = new CompraDetalleMdl();

        $reglas = [
            'nIdSucursal' => 'required',
            'nIdProveedor' => 'required'
        ];
        $reglasMsj = [
            'nIdSucursal' => [
                'required'   => 'Falta almac&eacute;n',
            ],
            'nIdProveedor' => [
                'required'   => 'Falta Proveedor',
            ],
        ];


        if (!$this->validate(
            [
                'nIdSucursal' => 'required',
                'nIdProveedor' => 'required'
            ]
        )) {
            $data['validation'] = $this->validator;
            helper(['cookie']);
            $data['navbar'] = get_cookie('mainMenu');
            echo view('templates/header', $data);
            echo view('compras/compramtto', $data);
            echo view('templates/footer', $this->dataMenu);
        } else {
            $builder = $model->builder();
            $r = [

                'dcompra' => $this->request->getVar('dcompra'),
                'nIdSucursal' => $this->request->getVar('nIdSucursal'),
                'nIdProveedor' => $this->request->getVar('nIdProveedor'),
                'fTotal' => $this->request->getVar('fTotal'),
                'nProductos' => $this->request->getVar('nProductos'),
            ];
            if ($this->request->getVar('nIdCompra') !== '-1') {
                $r['nIdCompra'] = $this->request->getVar('nIdCompra');
            }
            $model->save($r);
            $inserted = $model->insertID();
            foreach ($_SESSION['lstCompraArt'] as $rd) {
                $det = [
                    'nIdCompra' => $inserted,
                    'nIdArticulo' => $rd[0],
                    'fImporte' => $rd[1],
                    'fCantidad' => $rd[2],
                ];
                $detalle->save($det);
            }
            return redirect()->to(base_url('compras'));
        }
    }


    public function borra()
    {
        $model = new ComprasMdl();
        $model->delete($this->request->getVar('nIdCompra'));
        return json_encode(['ok' => '1']);
    }

    public function leeRegistro($id)
    {
        $model = new ComprasMdl();
        $reg =  $model->getRegistros($id);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function buscaNombre($sValor)
    {
        $model = new ComprasMdl();
        $reg =  $model->getRegistrosByField('nIdSucursal', $sValor);
        return json_encode(['ok' => '1', 'registro' => $reg]);
    }

    public function validaCampos()
    {

        $reglas = [
            'nIdSucursal' => 'required',
            'nIdProveedor' => 'required'
        ];
        $reglasMsj = [
            'nIdSucursal' => [
                'required'   => 'Falta almac&eacute;n',
            ],
            'nIdProveedor' => [
                'required'   => 'Proveedor',
            ],
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    public function agregaArticulo()
    {
        $session = session();

        if (!isset($_SESSION['lstCompraArt'])) {
            $_SESSION['lstCompraArt'] = array();
        }
        $_SESSION['lstCompraArt'][] = array(
            $this->request->getVar('nIdArticulo'),
            $this->request->getVar('dlArticulos0'),
            $this->request->getVar('nPrecio'),
            $this->request->getVar('nCant'),
        );
        $_SESSION['foc'] = 'art';

        return redirect()->to(base_url('compras/registrar'));
    }

    public function borraArticulo($id)
    {
        $session = session();
        $a = [];
        foreach ($_SESSION['lstCompraArt'] as $r) {
            if ($r[0] != $id) {
                $a[] = $r;
            }
        }
        $_SESSION['lstCompraArt'] = $a;
        $_SESSION['foc'] = 'art';
        return redirect()->to(base_url('compras/registrar'));
    }
}
