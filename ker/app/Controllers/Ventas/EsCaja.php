<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Ventas\EsCajaMdl;
use DateTime;
use Luecano\NumeroALetras\NumeroALetras;

class EsCaja extends BaseController
{

    public function index()
    {
        $this->validaSesion();
        //$this->validaTurnoCorte();
        if (($l = $this->validaTurnoCorte(true))) return $l;

        $model = new EsCajaMdl();
        $data = [
            'registros' => $model->getRegistros(false, true, $this->nIdSucursal),
            'pager' => $model->pager
        ];
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/esCaja', $data);
        echo view('templates/footer', $this->dataMenu);

        return;
    }

    public function accion($tipoaccion, $id = 0)
    {
        session();
        $this->validaTurnoCorte();
        $model = new EsCajaMdl();

        switch ($tipoaccion) {
            case 'a':
                $stitulo = 'Agrega';
                break;
            case 'b':
                $stitulo = 'Borra';
                break;
            case 'e':
                $stitulo = 'Edita';
                break;
            default:
                $stitulo = 'Ver';
        }

        $data = [
            'titulo' => $stitulo . ' Entrada/Salida de Caja',
            'frmURL' => base_url('movtoCajas/' . $tipoaccion .
                ($id !== '' ? '/' . $id : '')),
            'modo' => strtoupper($tipoaccion),
            'ingresoacaja' => $this->aPermiso['oIngresoACaja'] ?? 'NO',
            'id' => $id
        ];

        if (strtoupper($this->request->getMethod()) === 'POST') {
            if ($tipoaccion === 'b') {
                $model->delete($id);
                echo 'oK';
                return;
            }
            $a = $this->validaCampos();
            if ($a === false) {
                $r = [
                    'cTipoMov' => $this->request->getVar('cTipoMov'),
                    'sMotivo' => str_replace(
                        [chr(10), chr(9), chr(13)],
                        ' ',
                        $this->request->getVar('sMotivo')
                    ),
                    'nImporte' => $this->request->getVar('nImporte'),
                    'nIdSucursal' => $this->nIdSucursal
                ];
                if ($tipoaccion == 'e') {
                    $r['nIdEScaja'] = $id;
                }
                $model->save($r);
                // en E/S de cajas solo se agregan registros, es seguro usar el insert de corte de caja aquí
                $mdlCorte = new CorteCajaMdl();
                $idFolio = $model->getInsertID();
                $mdlCorte->guardaFolioMovimiento($this->nIdUsuario, $this->nIdSucursal, $idFolio, '2');
                $this->preparaImpresion($idFolio);
                echo 'oK';
                return;
            } else {
                $data['error'] = $a['amsj'];
            }
        } else {
            if ($tipoaccion !== 'a') {
                $data['registro'] =  $model->getRegistros($id);
            }
        }
        echo view('ventas/escajamtto', $data);
    }


    public function validaCampos()
    {
        $reglas = [
            'sMotivo' => 'required',
            'nImporte' => 'required|decimal|mayorDeCero',
            'sPersona' => 'required'
        ];
        $reglasMsj = [
            'sMotivo' => [
                'required'   => 'Falta el motivo del movimiento'
            ],
            'nImporte' => [
                'required'   => 'Falta el importe',
                'decimal' => 'Debe ser un numero',
                'mayorDeCero' => 'El importe debe ser mayor de cero',

            ],
            'sPersona' => [
                'required' => 'Falta el nombre de la persona quien recibe o entrega dinero'
            ]
        ];
        if (!$this->validate($reglas, $reglasMsj)) {
            return ['amsj' => $this->validator->getErrors()];
        }
        return false;
    }

    private function preparaImpresion($id)
    {
        $formato = new NumeroALetras();
        $fec = new DateTime();
        $nImp = round(floatval($this->request->getVar('nImporte')), 2);
        $formato->conector = 'PESOS CON';
        $sImp = '(** ' . $formato->toInvoice($nImp, 2, 'M.N.') . ' **)';
        if($this->request->getVar('cTipoMov') == 'E'){
            $tipoDoc = 'Recibo de otros ingresos';
            $recibi = $this->request->getVar('sPersona');
            $firma1 = 'E N T R E G A';
            $firma2 = 'R E C I B E';
        } else {
            $tipoDoc = 'Retiro efectivo de caja';
            $recibi = $this->sSucursal;
            $firma1 = 'R E C I B E';
            $firma2 = 'E N T R E G A';
        }
        $data = [
            'folio' => sprintf('%06d', intval($id)),
            'Usuario' => sprintf('%03d  ', intval($this->nIdUsuario)) . $this->sNombreUsuario,
            'fechahora' => $fec->format('d/m/Y H:i'),
            'tipoDoc' => $tipoDoc,
            'importe' => number_format($nImp, 2),
            'importeletras' => strtoupper($sImp) ,
            'motivo' => str_replace(
                [chr(10), chr(9), chr(13)],
                ' ',
                $this->request->getVar('sMotivo')
            ),
            'persona' => $this->request->getVar('sPersona'),
            'sucursal' => $this->sSucursal,
            'recibi' => $recibi,
            'firma1' => $firma1,
            'firma2' => $firma2,
            'urlret' => base_url('movtoCajas')

        ];
        $_SESSION['impresionRECCAJA'] = $data;
    }

    public function imprimeRecibo()
    {
        session();
        $data = $_SESSION['impresionRECCAJA'];
        echo view('templates/header', $this->dataMenu);
        echo view('ventas/imprimirReciboCaja', $data);
        echo view('templates/footer', $this->dataMenu);
    }
}
