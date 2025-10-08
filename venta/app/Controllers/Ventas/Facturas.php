<?php

namespace App\Controllers\Ventas;

use App\Controllers\BaseController;
use App\Models\Catalogos\ClienteMdl;
use App\Models\Catalogos\ConfiguracionMdl;
use App\Models\Catalogos\PrecioArticuloMdl;
use App\Models\Catalogos\RazonSocialMdl;
use App\Models\Catalogos\RegimenFiscalMdl;
use App\Models\Catalogos\SucursalMdl;
use App\Models\Catalogos\TipoPagoMdl;
use App\Models\Catalogos\UsoCfdiMdl;
use App\Models\Ventas\CorteCajaMdl;
use App\Models\Ventas\FacturasMdl;
use App\Models\Ventas\VentasDetMdl;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use DateTime;
use DOMDocument;
use DOMElement;
use DOMNode;
use SoapClient;
use SoapFault;
use SoapParam;
use XSLTProcessor;

class Facturas extends BaseController
{
    private array $aXmlDatFactura = [];     // usado en la impresion de la factura
    private array $aFacturasAprocesar;
    private SucursalMdl $mdlSucursal;
    private FacturasMdl $mdlFactura;
    private PrecioArticuloMdl $mdlPrecio;
    private array $aDataEmpresa;
    private array $datosParaFactura;
    public array $aError = [];

    private DOMDocument $Xml;

    private SoapClient $cliSOAP;
    // private string $URItimbra = 'http://facturandote.com/facturandote-wsdemo/facturandotewstimbre40demo.php?wsdl';
    // private string $URIcancela = 'http://facturandote.com/facturandote-wsdemo/facturandote0wstimbre40demo.php?wsdl';

    private string $URItimbra = 'https://srv3.acphomedesk.com/productos/cfdi.php?wsdl';
    private string $URIcancela = 'https://srv3.acphomedesk.com/productos/cfdi.php?wsdl';
    private string $certificado;
    private string $keyprivada;
    private string $keyprivadapem;
    private string $cadenaOriginal;
    private DOMDocument $xmlFirmado;
    private $pdfFirmadoBase64;
    private string $archivoDestino;
    private string $archivoDestinoOri;
    private string $idPAC;
    private string $pswPAC;
    private string $archivoEstado;

    private int $nFolioRemisionEnProceso;

    public function regeneraPDF($idFactura)
    {
        // if ($this->conectaParaTimbrar() === false) return false;
        // $arrFolios = $this->initDatosFactura($idFactura);

        // $datUser = 'admin69227';
        // $datPassword = md5('Aa93646$');
        // $datConexion = 'TC_XML_40';
        // $datServicio = 4;
        // $datVersion = round(floatval(4.0), 2);
        // $datData = base64_encode($cadXML);
        // try {
        //     $result = $this->cliSOAP->TimbrarCFDI(
        //         new SoapParam($datUser, 'user'),
        //         new SoapParam($datPassword, 'password'),
        //         new SoapParam($datConexion, 'conexion'),
        //         new SoapParam($datServicio, 'servicio'),
        //         new SoapParam($datVersion, 'version_cfdi'),
        //         new SoapParam($datData, 'data')
        //     );
        //     // file_put_contents('lastreqB1.xml', $this->cliSOAP->__getLastRequest());
        //     // file_put_contents('lastresB1.xml', $this->cliSOAP->__getLastResponse());
        // } catch (SoapFault $th) {
        //     $this->aError[] = 'SOAP error en la transmision al timbrar. ' .
        //         'Codigo (' . $th->faultcode . ')  ' .
        //         'mensaje (' . $th->faultstring . ')';
        //     return false;
        // }
    }

    public function imprimepdf($rutaDest = false)
    {
        // $this->validaSesion();

        // $this->mdlSucursal = new SucursalMdl();
        // $regDatSuc = $this->mdlSucursal->getRegistros($this->nIdSucursal);
        // // $mdlConfig = new RazonSocialMdl();   //    ConfiguracionMdl($db);

        // $sDatEmpresa = $mdlConfig->bloqueaParaFacturar($regDatSuc['nIdRazonSocial'], false);
        // if ($sDatEmpresa === false) {
        //     return json_encode([
        //         'ok' => '0',
        //         'msj' => 'Posiblemente otro usuario está usando el proceso, intente de nuevo más tarde.'
        //     ]);
        // };




        $this->facturaXmlAarray($rutaDest);
        $mdlConfig = new ConfiguracionMdl();
        // se leen los datos de la empresa o sucursal sin bloquear registro
        $tmpDat = $mdlConfig->bloqueaParaFacturar(false);

        $this->aDataEmpresa = explode('|', $tmpDat['sValor']);

        $mdlSucursal = new SucursalMdl();
        $regSucursal = $mdlSucursal->getRegistros($this->nIdSucursal);

        $cliMdl = new ClienteMdl();
        $cli = $cliMdl->getRegistros(15);
        $data = [
            'cli' => $cli,
            'enviarA' => 'Cigfrido',
            'idUsuario' => $this->nIdUsuario,
            'Dat' => $this->aXmlDatFactura,
            'DatEmp' =>  $this->aDataEmpresa,
            'DatSuc' => $regSucursal,
            'archDestino' => $rutaDest
        ];
        echo view('ventas/imprimeFactura', $data);
        if ($rutaDest === false) $this->response->setContentType('application/pdf');
    }

    public function procesacorte($idCorte, $tipo)
    {
        $this->validaSesion();
        if ($tipo !== 'C' && $tipo !== 'V') {
            return json_encode([
                'ok' => '0',
                'msj' => 'Tipo de proceso no coincide.'
            ]);
        }
        error_reporting(E_ALL);
        $mdlCorte = new CorteCajaMdl();
        $arrFacturasParaTimbrar = $mdlCorte->getCorteFacturasParaProcesar($idCorte, $tipo);   // proceso de cortedecaja
        if (count($arrFacturasParaTimbrar) == 0) {
            return json_encode([
                'ok' => '0',
                'msj' => 'No hay facturas para timbrar.'
            ]);
        }
        $cError = $this->verificaDatosParaFactura($arrFacturasParaTimbrar);
        if ($cError != '') {
            return json_encode([
                'ok' => '0',
                'msj' => $cError
            ]);
        }
        $aFacturas = [];
        foreach ($arrFacturasParaTimbrar as $v) $aFacturas[] = [
            $v['nIdFacturas'],
            $v['nFolioFactura'],
            $v['sSerie']
        ];

        if ($this->procesaFacturasPorTimbrar($aFacturas) === true) {
            return json_encode([
                'ok' => '1'
            ]);
        } else {
            $cad = '';
            foreach ($this->aError as $v) $cad .= $v;
            return json_encode([
                'ok' => '0',
                'msj' => $cad
            ]);
        }
    }

    private function verificaDatosParaFactura(&$arrDat)
    {
        /*
        f.nIdFacturas, v.nTotal, c.sNombre, v.nFolioRemision, ' .
                'v.bVariasFacturas, f.nIdVentas, v.nIdCliente
        */
        $mdlDet = new VentasDetMdl();
        $mdlCliente = new ClienteMdl();
        $arrCli = [];
        $arrArt = [];
        $cad = '';
        foreach ($arrDat as $v) {
            $regCli = $mdlCliente->getRegistros($v['nIdCliente']);
            if (
                strlen(trim($regCli['sRFC'])) < 6 ||
                trim($regCli['email']) == '' ||
                trim($regCli['cCP']) == '' ||
                trim($regCli['cIdRegimenFiscal']) == ''
            ) {
                if (!isset($arrCli[$v['nIdCliente']])) {
                    $arrCli[$v['nIdCliente']] = '';
                    $cad .= 'Datos fiscales incompletos del cliente ' . $regCli['sNombre'] . '<br>';
                }
            }
            $regDet = $mdlDet->getDetalleFiscal($v['nIdVentas']);
            //  'a.nIdArticulo, a.sDescripcion, a.sCveProdSer, a.sCveUnidad, a.cUnidad
            foreach ($regDet as $d) {
                if (trim($d['sCveProdSer']) == '' || trim($d['sCveUnidad']) == '' || trim($d['cUnidad']) == '') {
                    if (!isset($arrArt[$d['nIdArticulo']])) {
                        $arrArt[$d['nIdArticulo']] = '';
                        $cad .= 'Datos fiscales incompletos del articulo ' . $d['sDescripcion'] . '<br>';
                    }
                }
            }
        }

        return $cad;
    }

    public function procesaFacturasPorTimbrar(array &$aFacturas)
    {
        $this->mdlSucursal = new SucursalMdl();
        $this->mdlFactura = new FacturasMdl();
        $this->mdlPrecio = new PrecioArticuloMdl();
        $regDatSuc = $this->mdlSucursal->getRegistros($this->nIdSucursal);

        $db = db_connect(null, false);

        $mdlConfig = new RazonSocialMdl($db);   //    ConfiguracionMdl($db);

        $this->aFacturasAprocesar = $aFacturas;
        if ($this->asignaFoliosFactura() === false) return false;

        $mdlConfig->db->transStart();

        $sDatEmpresa = $mdlConfig->bloqueaParaFacturar($regDatSuc['nIdRazonSocial']);
        if ($sDatEmpresa === false) {
            return json_encode([
                'ok' => '0',
                'msj' => 'Posiblemente otro usuario está usando el proceso, intente de nuevo más tarde.'
            ]);
        };
        // $datApikeyUser = 'TAS1110117U7';
        // $datApikey = 'dc8c0475-2570-4e94-844e-fff8f5b5fc14';

        // $datApikeyUser = 'TAC1110116D7';
        // $datApikey = '247300a1-fe39-4d65-9ee7-ad111fb6b870';

        // if (isset($aDat['4']) === false)
        //     $this->datosParaFactura['apikeyuser'] = 'TAC1110116D7';
        // else
        //     $this->datosParaFactura['apikeyuser'] = $this->datosParaFactura['emiRfc'];

        $this->procesaFacturas();
        if (count($this->aError) == 0) {
            $mdlConfig->db->transComplete();
            return true;
        } else {
            $mdlConfig->db->transRollback();
            return false;
        }
    }

    private function asignaFoliosFactura()
    {
        // $v['nIdFacturas'], $v['nFolioFactura'], $v['sSerie']

        $this->mdlSucursal->db->transStart();
        $rSuc = $this->mdlSucursal->leeFolio($this->nIdSucursal);
        if ($rSuc === false) {
            $this->aError[] = 'Posiblemente otro usuario está usando el proceso de facturacion, intente de nuevo más tarde. ';
            return false;
        };
        $this->idPAC = $rSuc['sIdPAC'];
        $this->pswPAC = $rSuc['sPswPAC'];

        $nFolio = intval($rSuc['nFolioFactura']);
        $sSerie = trim($rSuc['sSerie']);

        foreach ($this->aFacturasAprocesar as $k => $v) {
            if (intval($v[1]) > 0) continue;

            $this->mdlFactura->set([
                'nFolioFactura' => $nFolio,
                'sSerie' => $sSerie
            ])->where('nIdFacturas', intval($v[0]))
                ->update();
            $this->aFacturasAprocesar[$k][1] = $nFolio;
            $this->aFacturasAprocesar[$k][2] = $sSerie;
            $nFolio++;
        }
        $this->mdlSucursal->addFolioFac($this->nIdSucursal, $nFolio);
        $this->mdlSucursal->db->transComplete();
    }

    public function procesaFacturas()
    {
        if ($this->conectaParaTimbrar() === false) return false;
        $nInd = 0;
        foreach ($this->aFacturasAprocesar as $aV) {
            $nInd++;
            $idFactura = $aV[0];
            // se valida si esta cancelado o no
            $this->mdlSucursal->db->transStart();
            // $rSuc = $this->mdlSucursal->leeFolio($this->nIdSucursal);
            // if ($rSuc === false) {
            //     $this->aError[] = 'Posiblemente otro usuario está usando el proceso de facturacion, intente de nuevo más tarde. ';
            //     return false;
            // };
            // $this->idPAC = $rSuc['sIdPAC'];
            // $this->pswPAC = $rSuc['sPswPAC'];

            /*
            $arrRetorno['nIdFacturas'] = $aDatos[0]['nIdFacturas'];
            $arrRetorno['nFolioFactura'] = $aDatos[0]['nFolioFactura'];
            $arrRetorno['nIdVentas'] = $aDatos[0]['nIdVentas'];
            $arrRetorno['sUUID'] = $aDatos[0]['sUUID'];
            $arrRetorno['nFolioRemision'] = $aDatos[0]['nFolioRemision'];
            */
            $arrFolios = $this->initDatosFactura($idFactura);
            if (trim($arrFolios['sUUID']) != '') {
                $this->mdlSucursal->db->transRollback();
                continue;   // ya esta timbrado
            }

            //if ($this->leeCertificados('assets/certis/') == false) return false; // no aplica para este PAC
            $this->nFolioRemisionEnProceso = $arrFolios['nFolioRemision'];
            // $this->asignaFolio(
            //     $arrFolios['nFolioFactura'],
            //     $idFactura,
            //     $rSuc['nFolioFactura'],
            //     $rSuc['sSerie'],
            //     $this->nIdSucursal
            // );
            // calcula factura
            $this->calculaFactura(
                $arrFolios['nIdFacturas'],
                $arrFolios['nFolioFactura'],
                $arrFolios['nIdVentas'],
                $arrFolios['sUUID']
            );

            if ($this->generaXML() === false) {
                $this->mdlSucursal->db->transRollback();
                array_splice($this->aError, 0, 0, 'Error en Remision No. ' . $this->nFolioRemisionEnProceso . ': ');
                return false;
            }
            // $this->mdlSucursal->db->transRollback();

            // $this->actualizaDatosFacturaSinTimbrado($idFactura); // para pruebas sin mandar a timbrar
            $this->actualizaDatosFactura($idFactura);
            $this->generaArchivos();
            // solucion, enviar el correo al finalizar la facturacion. para que no se retrase el timbrado
            $this->enviaCorreo();

            $this->mdlSucursal->db->transComplete();
            $this->borraArchivos();
        }
    }

    private function actualizaDatosFactura($idFactura)
    {
        $nodoTFD = $this->xmlFirmado->getElementsByTagNameNS('http://www.sat.gob.mx/TimbreFiscalDigital', 'TimbreFiscalDigital')->item(0);
        $this->leeAtributo($nodoTFD, 'UUID', false, $this->datosParaFactura);
        $this->leeAtributo($nodoTFD, 'FechaTimbrado', false, $this->datosParaFactura);
        $fechaTimbrado = (new DateTime($this->datosParaFactura['FechaTimbrado']))->format('Y-m-d H:i:s');
        $fechaFactura = (new DateTime($this->datosParaFactura['fecha']))->format('Y-m-d H:i:s');
        $this->mdlFactura->set([
            'sUUID' => $this->datosParaFactura['UUID'],
            'dFechaTimbrado' => $fechaTimbrado,
            'dFechaFactura' => $fechaFactura
        ])->where('nIdFacturas', intval($idFactura))
            ->update();

        $this->xmlFirmado->formatOutput = false;
        $this->xmlFirmado->preserveWhiteSpace = false;
        $cadXML = $this->xmlFirmado->saveXML();
        // $this->mdlFactura->saveXML($idFactura, $cadXML, $this->pdfFirmadoBase64);
    }

    private function generaArchivos()
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'tmp');
        $this->archivoDestinoOri = $temp_file;
        $arrInfo = pathinfo($temp_file);
        $this->archivoDestino = $arrInfo['dirname'] . '/' . $arrInfo['filename'];
        // se genera el xml
        $this->xmlFirmado->formatOutput = false;
        $this->xmlFirmado->preserveWhiteSpace = false;
        $this->xmlFirmado->save($this->archivoDestino . '.xml');
        file_put_contents($this->archivoDestino . '.pdf', base64_decode($this->pdfFirmadoBase64));
        // file_put_contents($this->archivoDestino . 'base64.pdf', $this->pdfFirmadoBase64);
        // $this->imprimepdf($this->archivoDestino);
    }

    private function borraArchivos()
    {
        unlink($this->archivoDestinoOri);
        unlink($this->archivoDestino . '.pdf');
        unlink($this->archivoDestino . '.xml');
    }

    private function actualizaDatosFacturaSinTimbrado($idFactura)
    {
        $fecha = (new DateTime())->format('Y-m-d H:i:s');
        $this->mdlFactura->set([
            'sUUID' => 'UUID()',
            'dFechaTimbrado' => $fecha
        ], false)->where('nIdFacturas', intval($idFactura))
            ->update();

        $this->xmlFirmado->formatOutput = false;
        $this->xmlFirmado->preserveWhiteSpace = false;
        $cadXML = $this->xmlFirmado->saveXML();
        $this->mdlFactura->saveXML($idFactura, $cadXML, $this->pdfFirmadoBase64 ?? '');
    }

    private function initDatosFactura($idFactura)
    {
        $arrRetorno = [];
        $aDatos = $this->mdlFactura->getDatosFactura($idFactura);   // devuelve Ids de factura, ventas, UUID y Folio Factura
        $this->datosParaFactura['emiRfc'] = $aDatos[0]['rzsRFC'];
        $this->datosParaFactura['emiNom'] = $aDatos[0]['rzsRazonSocial'];
        $this->datosParaFactura['emiRegFis'] = $aDatos[0]['rfcIdRegimenFiscal'];
        $this->datosParaFactura['emiRegFisDes'] = $aDatos[0]['rfsDescripcion'];
        $this->datosParaFactura['noCertificado'] = $aDatos[0]['rzsCertificado'];
        $this->datosParaFactura['apikey'] = ''; // no aplica para este pac

        $this->datosParaFactura['recIdUsoCFDI'] = $aDatos[0]['cUsoCFDI'];
        $this->datosParaFactura['recDesUsoCFDI'] = $aDatos[0]['sDesUsoCfdi'];
        $this->datosParaFactura['metodoPago'] = $aDatos[0]['cMetodoPago'];
        $this->datosParaFactura['folio'] = $aDatos[0]['nFolioFactura'];
        $this->datosParaFactura['serie'] = $aDatos[0]['sSerie'];
        if ($aDatos[0]['dFechaFactura'] == '') {
            $this->datosParaFactura['fecha'] = (new DateTime())->format('Y-m-d\TH:i:s');
        } else {
            $this->datosParaFactura['fecha'] = (new DateTime($aDatos[0]['dFechaFactura']))->format('Y-m-d\TH:i:s');
        }
        $this->datosParaFactura['lugarExpedicion'] = $aDatos[0]['lugarExpedicion'];
        $this->datosParaFactura['sello'] = '@';
        $this->datosParaFactura['certificado'] = '@';
        $this->datosParaFactura['recRfc'] = $aDatos[0]['sRFC'];
        if ($aDatos[0]['cTipoCliente'] == 'P') {
            if ($aDatos[0]['sNomPubGen'] == '')
                $this->datosParaFactura['recNom'] = $aDatos[0]['sNombre'];
            else
                $this->datosParaFactura['recNom'] = $aDatos[0]['sNomPubGen'];
        } else {
            $this->datosParaFactura['recNom'] = $aDatos[0]['sNombre'];
        }
        $this->datosParaFactura['recDomFis'] = $aDatos[0]['cTipoCliente'] == 'P' ? $aDatos[0]['lugarExpedicion'] : $aDatos[0]['cCP'];
        $this->datosParaFactura['recRegFis'] = $aDatos[0]['cIdRegimenFiscal'];
        $this->datosParaFactura['recDesRegFis'] = $aDatos[0]['sRegFis'];
        $this->datosParaFactura['email'] = $aDatos[0]['cTipoCliente'] == 'P' ? $aDatos[0]['emailSucursal'] : $aDatos[0]['email'];
        $this->datosParaFactura['emailSuc'] = $aDatos[0]['emailSucursal'];
        $this->datosParaFactura['UUID'] = $aDatos[0]['sUUID'];
        $this->datosParaFactura['cIdTipoRelacion'] = $aDatos[0]['cIdTipoRelacion'];
        $rUU = $this->mdlFactura->getDatosUUIDrel($idFactura);
        $aUU = [];
        foreach ($rUU as $v) $aUU[] = $v['sUUID'];
        $this->datosParaFactura['UUIDrel'] = $aUU;
        $arrRetorno['nIdFacturas'] = $aDatos[0]['nIdFacturas'];
        $arrRetorno['nFolioFactura'] = $aDatos[0]['nFolioFactura'];
        $arrRetorno['nIdVentas'] = $aDatos[0]['nIdVentas'];
        $arrRetorno['sUUID'] = $aDatos[0]['sUUID'];
        $arrRetorno['nFolioRemision'] = $aDatos[0]['nFolioRemision'];

        if ($this->datosParaFactura['metodoPago'] == 'PPD') {
            $this->datosParaFactura['formaPago'] = '99';
            $this->datosParaFactura['condicionesPago'] = 'CREDITO';
            // ($this->datosParaFactura['metodoPago'] == 'PPD' ? '99' : $aDatosPagos[0]['sTipoSAT']);
        } else {
            $aDatosPagos = $this->mdlFactura->getDatosPagos(
                $aDatos[0]['nIdVentas'],
                $aDatos[0]['tipoPagoFactura'] == '0' ? false : $aDatos[0]['tipoPagoFactura']
            );
            $this->datosParaFactura['formaPago'] = $aDatosPagos[0]['sTipoSAT'];
            $this->datosParaFactura['condicionesPago'] = '';
        }
        return $arrRetorno;
    }

    private function asignaFolio($nFolioEnfactura, $idFac, $folioAasignar, $sSerie, $idSuc)
    {
        if ($nFolioEnfactura == '0') {
            $this->mdlFactura->set([
                'nFolioFactura' => $folioAasignar,
                'sSerie' => $sSerie
            ])->where('nIdFacturas', intval($idFac))
                ->update();
            $this->datosParaFactura['folio'] = $folioAasignar;
            $this->datosParaFactura['serie'] = $sSerie;
            $this->mdlSucursal->addFolioFac($idSuc);
        }
        return;
    }

    private function calculaFactura($idFac, $idFolio, $idVen, $sUUID)
    {
        // calcular la factura
        $aImpuesto = [
            'IVA',
            '002',
            'Traslado',
            'Tasa',
            0.16,
            1.16
        ];
        $factorIvaDesglose = 1.16;
        // leo el detalle y se calcula
        $aRegDet = $this->mdlFactura->leeDetalleFactura($idFac, $idVen);
        $nSubTotal = 0;
        $Impuestos = 0;

        // se calcula los importes
        $nRegistros = count($aRegDet);
        for ($nInd = 0; $nInd < $nRegistros; $nInd++) {
            $nCant = round(floatval($aRegDet[$nInd]['nCant']), 3);
            if ($nCant == 0) continue;
            $nCantDetRemision = round(floatval($aRegDet[$nInd]['nCantDetRemision']), 3);
            $nPrecio = round(floatval($aRegDet[$nInd]['nPrecio']), 4);
            $aplicaPrecioTapadoFactura = false;
            $importePorUnidadTapado = 0;
            if ($aRegDet[$nInd]['cPrecioTapadoFactura'] == '1') {
                $importeTapado = round(floatval($aRegDet[$nInd]['nImporteTapadoFactura']), 2);
                if ($importeTapado > 0) {
                    $aplicaPrecioTapadoFactura = true;
                    $importePorUnidadTapado = $importeTapado / $nCantDetRemision;
                }
            }
            if ($aplicaPrecioTapadoFactura) {
                $nImporteConDescuentoTotal = $nCant * $importePorUnidadTapado;
            } else {
                $nImporte = round($nCantDetRemision * $nPrecio, 4);
                $nDescuentoTotal = round(floatval($aRegDet[$nInd]['nDescuentoTotal']), 2);
                $nComisionTotal = round(floatval($aRegDet[$nInd]['nImpComisionTotal']), 2);
                $nImporteConDescuentoTotal = $nImporte - $nDescuentoTotal + $nComisionTotal;
                $nImporteXArticulo = $nImporteConDescuentoTotal / $nCantDetRemision;
                $nImporteConDescuentoTotal = $nCant * $nImporteXArticulo;
            }
            $nImporteDesglosado = round($nImporteConDescuentoTotal / $factorIvaDesglose, 6);
            $nPrecioDesglosado = round($nImporteDesglosado / $nCant, 6);
            // $nImporteConDesglose = round($nPrecioDesglosado * $nCant, 6);
            // $nIVA1 = round($nImporteConDescuentoTotal - $nImporteConDesglose, 6);
            $nIVA = round($nImporteDesglosado * .16, 6);

            // $nImporteTotalProducto = round($nImporte + $nIVA, 2);
            $aRegDet[$nInd]['precioFactura'] = $nPrecioDesglosado;
            $aRegDet[$nInd]['importeFactura'] = $nImporteDesglosado;
            $aRegDet[$nInd]['ivaFactura'] = $nIVA;
            $nSubTotal += $nImporteDesglosado;
            $Impuestos += $nIVA;
        }
        $this->datosParaFactura['conceptos'] = $aRegDet;
        $this->datosParaFactura['totalIVA'] = $Impuestos;
        $this->datosParaFactura['subTotal'] = $nSubTotal;
    }

    public function generaXML()
    {
        $this->Xml = new DOMDocument('1.0', 'UTF-8');
        $this->xmlFormaComprobante();
        // $this->Xml->load('assets/tmp/prueba2.xml');
        // if ($this->validaXML() === false) return false;
        // $this->cadenaOriginal = $this->extraeCadenaOriginalFactura();
        // $this->sellaFactura();
        if ($this->timbraXML() === false) return false;
        // $this->simulaRespuestaTimbrado();
    }

    private function extraeCadenaOriginalFactura()
    {
        $this->Xml->formatOutput = false;
        $sXmlenTexto = $this->Xml->saveXML();

        $xml = new DOMDocument("1.0", "UTF-8");
        $xml->loadXML($sXmlenTexto);

        $xmlXslt = new DOMDocument("1.0", "UTF-8");
        $xmlXslt->load('assets/facturaV4/cadenaoriginal_4_0.xslt');
        $xsl = new XSLTProcessor();
        $xsl->importStylesheet($xmlXslt);

        return $xsl->transformToXml($xml);
    }

    private function sellaFactura()
    {
        $pkeyidpem = openssl_get_privatekey($this->keyprivadapem);
        openssl_sign($this->cadenaOriginal, $crypttextpem, $pkeyidpem, OPENSSL_ALGO_SHA256);
        $sellopem = base64_encode($crypttextpem);      // lo codifica en formato base64
        $nodoComprobante = $this->Xml->getElementsByTagName('cfdi:Comprobante')->item(0);
        $this->xmlAtributos($nodoComprobante, [
            'Sello' => $sellopem,
            'Certificado' => $this->certificado
        ]);
    }

    private function leeCertificados($ruta)
    {
        error_reporting(E_ALL ^ E_WARNING);
        $this->certificado = base64_encode(file_get_contents($ruta . $this->datosParaFactura['noCertificado'] . '.cer'));
        $this->keyprivadapem = file_get_contents($ruta . $this->datosParaFactura['noCertificado'] . '.key.pem');
        error_reporting(E_ALL);
        if ($this->certificado == '' || $this->keyprivadapem === false) {
            $this->aError[] = 'No se pudo inicializar los certificados. ';
            return false;
        }
        return true;
    }

    private function conectaParaTimbrar()
    {
        try {
            $this->cliSOAP = new SoapClient($this->URItimbra, [
                'soap_version' => SOAP_1_1,
                'use' => SOAP_LITERAL
            ]);
        } catch (SoapFault $th) {
            //            iconv('UTF-8', 'ISO-8859-1', 'Este documento es una representación impresa de un CFDI'),
            $this->aError[] = 'No se pudo inicializar el conector SOAP para timbrar. ' . $th->faultstring;
            return false;
        }
        return true;
    }

    private function timbraXML()
    {
        $this->Xml->formatOutput = false;
        $this->Xml->preserveWhiteSpace = false;
        $cadXML = $this->Xml->saveXML();
        // $this->Xml->save('pasoRem' . $this->nFolioRemisionEnProceso .'.xml');
        // $this->aError[] = 'Prueba xml. ' .
        //     'Codigo (ninguno)  ' .
        //     'mensaje (ninguno)';
        // return true;

        $datUser = $this->idPAC;
        $datPassword = md5($this->pswPAC);
        $datConexion = 'TC_XML_40';
        $datServicio = 4;
        $datVersion = round(floatval(4.0), 2);
        $datData = base64_encode($cadXML);
        try {
            $result = $this->cliSOAP->TimbrarCFDI(
                new SoapParam($datUser, 'user'),
                new SoapParam($datPassword, 'password'),
                new SoapParam($datConexion, 'conexion'),
                new SoapParam($datServicio, 'servicio'),
                new SoapParam($datVersion, 'version_cfdi'),
                new SoapParam($datData, 'data')
            );
            // file_put_contents('lastreqB1.xml', $this->cliSOAP->__getLastRequest());
            // file_put_contents('lastresB1.xml', $this->cliSOAP->__getLastResponse());
        } catch (SoapFault $th) {
            $this->aError[] = 'SOAP error en la transmision al timbrar. ' .
                'Codigo (' . $th->faultcode . ')  ' .
                'mensaje (' . $th->faultstring . ')';
            return false;
        }
        return $this->procesaRespuestaTimbrado($result);
    }

    private function procesaRespuestaTimbrado(&$obj)
    {
        if ($obj->codigo != '200' && $obj->codigo != '005' && $obj->codigo != '5') {
            $this->aError[] = $obj->mensaje;
            return false;
        }
        $c = base64_decode($obj->cfdi);
        // file_put_contents('paso2.xml', $c);
        $this->pdfFirmadoBase64 = $obj->pdf;

        $this->xmlFirmado = new DOMDocument("1.0", "UTF-8");
        $this->xmlFirmado->loadXML($c);
        if ($this->xmlFirmado->xmlEncoding === null) $this->xmlFirmado->encoding = 'UTF-8';
        return true;
    }

    private function simulaRespuestaTimbrado()
    {

        $this->datosParaFactura['UUID'] = uniqid('tmp');
        $this->xmlFirmado = new DOMDocument("1.0", "UTF-8");
        $this->xmlFirmado->loadXML($this->Xml->saveXML());
        if ($this->xmlFirmado->xmlEncoding === null) $this->xmlFirmado->encoding = 'UTF-8';
        return true;
    }

    private function validaXML()
    {
        $this->Xml->formatOutput = true;
        $xmlAux = new DOMDocument("1.0", "UTF-8");
        $texto = $this->Xml->saveXML();
        $xmlAux->loadXML($texto);
        $xmlAux->save('arch.xml');
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $ok = $xmlAux->schemaValidate("assets/facturaV4/cfdv40.xsd");
        if (!$ok) {
            $aErr = [];
            $lineas = explode("\n", $texto);

            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $Val = $lineas[$error->line - 1] . "<br>";
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $Val .= "Warning $error->code: ";
                        break;
                    case LIBXML_ERR_ERROR:
                        $Val .= "Error $error->code: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $Val .= "Fatal Error $error->code: ";
                        break;
                }
                $Val .= trim($error->message) . '<br><strong>Linea:</strong>' . $error->line . '<br>';
                $aErr[] = $Val;
            }
            $this->aError = $aErr;
        }
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        return $ok;
    }


    private function xmlFormaComprobante()
    {
        $nodoComprobante = null;
        $elemento = $this->Xml->createElement('cfdi:Comprobante');
        $nodoComprobante = $this->Xml->appendChild($elemento);
        //$LugarExp = utf8_encode($aDatGen['regp']['cDFlocalidad'] . ', ' . $aDatGen['regp']['cDFestado']);
        $nDecimales = 2;
        $aTmp = [
            'xmlns:cfdi' => 'http://www.sat.gob.mx/cfd/4',
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation' => 'http://www.sat.gob.mx/cfd/4 ' .
                'http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd ',
            'Version' => '4.0',
            'Serie' => $this->datosParaFactura['serie'],
            'Folio' => $this->datosParaFactura['folio'],
            'Fecha' => $this->datosParaFactura['fecha'],
            'Sello' => '@',
            'FormaPago' => $this->datosParaFactura['formaPago'],
            'NoCertificado' => $this->datosParaFactura['noCertificado'],
            'Certificado' => '@',
            'SubTotal' => number_format(round($this->datosParaFactura['subTotal'], 2), $nDecimales, '.', ''),
            'Descuento' => number_format(0, 2, '.', ''),
            'Moneda' => 'MXN',
            'Total' => number_format((round($this->datosParaFactura['subTotal'], 2) + round($this->datosParaFactura['totalIVA'], 2)), $nDecimales, '.', ''),
            'TipoDeComprobante' => 'I',
            'Exportacion' => '01',
            'MetodoPago' => $this->datosParaFactura['metodoPago'],
            'LugarExpedicion' => $this->datosParaFactura["lugarExpedicion"]
        ];
        if ($this->datosParaFactura['condicionesPago'] != '') {
            $aTmp['CondicionesDePago'] = $this->datosParaFactura['condicionesPago'];
        }

        $this->xmlAtributos($nodoComprobante, $aTmp);


        if (count($this->datosParaFactura["UUIDrel"]) > 0) {
            $elemento = $this->Xml->createElement('cfdi:CfdiRelacionados');
            $nodoCfdisRel = $nodoComprobante->appendChild($elemento);
            $this->xmlAtributos($nodoCfdisRel, array(
                'TipoRelacion' => $this->datosParaFactura["cIdTipoRelacion"]
            ));

            foreach ($this->datosParaFactura["UUIDrel"] as $v) {
                $elemento = $this->Xml->createElement('cfdi:CfdiRelacionado');
                $nodoCfdiRel = $nodoCfdisRel->appendChild($elemento);
                $this->xmlAtributos($nodoCfdiRel, array(
                    'UUID' => $v
                ));
            }
        }

        $elemento = $this->Xml->createElement('cfdi:Emisor');
        $nodoEmisor = $nodoComprobante->appendChild($elemento);
        $this->xmlAtributos($nodoEmisor, array(
            'Rfc' => $this->datosParaFactura["emiRfc"],
            'Nombre' => $this->datosParaFactura["emiNom"],
            "RegimenFiscal" => $this->datosParaFactura["emiRegFis"]
        ));

        $elemento = $this->Xml->createElement('cfdi:Receptor');
        $nodoReceptor = $nodoComprobante->appendChild($elemento);
        $this->xmlAtributos($nodoReceptor, array(
            'Rfc' => $this->datosParaFactura["recRfc"],
            'Nombre' => $this->datosParaFactura["recNom"],
            'DomicilioFiscalReceptor' => $this->datosParaFactura["recDomFis"],
            'RegimenFiscalReceptor' => $this->datosParaFactura["recRegFis"],
            'UsoCFDI' => $this->datosParaFactura["recIdUsoCFDI"]
        ));

        $elemento = $this->Xml->createElement('cfdi:Conceptos');
        $nodoConceptos = $nodoComprobante->appendChild($elemento);

        foreach ($this->datosParaFactura["conceptos"] as $v) {
            $elemento = $this->Xml->createElement('cfdi:Concepto');
            $nodoConcepto = $nodoConceptos->appendChild($elemento);
            $this->xmlAtributos($nodoConcepto, array(
                'ClaveProdServ' => $v['sCveProdSer'],
                'NoIdentificacion' => $v['nIdArticulo'],
                'Cantidad' => $v['nCant'],
                'ClaveUnidad' => $v['sCveUnidad'],
                'Unidad' => $v['cUnidad'],
                'Descripcion' => $v['sDescripcion'],
                'ValorUnitario' => number_format($v['precioFactura'], 6, '.', ''),
                'Importe' => number_format($v['importeFactura'], 6, '.', ''),
                'Descuento' => number_format(0, $nDecimales, '.', ''),
                'ObjetoImp' => '02'
            ));
            $elemento = $this->Xml->createElement('cfdi:Impuestos');
            $nodoImpuestos = $nodoConcepto->appendChild($elemento);
            $elemento = $this->Xml->createElement('cfdi:Traslados');
            $nodoTraslados = $nodoImpuestos->appendChild($elemento);
            $elemento = $this->Xml->createElement('cfdi:Traslado');
            $nodoTraslado = $nodoTraslados->appendChild($elemento);
            $this->xmlAtributos($nodoTraslado, array(
                'Base' => number_format($v['importeFactura'], 6, '.', ''),
                'Impuesto' => '002',
                'TipoFactor' => 'Tasa',
                'TasaOCuota' => '0.160000',
                'Importe' => number_format(round($v['importeFactura'] * .16, 6), 6, '.', '')
            ));
        }
        $elemento = $this->Xml->createElement('cfdi:Impuestos');
        $nodoImpuestos = $nodoComprobante->appendChild($elemento);
        $this->xmlAtributos($nodoImpuestos, array(
            'TotalImpuestosTrasladados' => number_format($this->datosParaFactura["totalIVA"], 2, '.', '')
        ));
        $elemento = $this->Xml->createElement('cfdi:Traslados');
        $nodoTraslados = $nodoImpuestos->appendChild($elemento);
        $elemento = $this->Xml->createElement('cfdi:Traslado');
        $nodoTraslado = $nodoTraslados->appendChild($elemento);
        $this->xmlAtributos($nodoTraslado, array(
            'Base' => number_format($this->datosParaFactura["subTotal"], $nDecimales, '.', ''),
            'Impuesto' => '002',
            'TipoFactor' => 'Tasa',
            'TasaOCuota' => '0.160000',
            'Importe' => number_format($this->datosParaFactura["totalIVA"], $nDecimales, '.', '')
        ));
    }

    private function xmlAtributos(&$nodo, $aAttr)
    {
        foreach ($aAttr as $key => $val) {
            $val = htmlspecialchars($val, ENT_COMPAT | ENT_HTML401, "UTF-8"); // regla
            $val = preg_replace('/(\s\s+)|\R|\t/', ' ', $val);  // regla 5a 5c
            $val = trim($val);  // regla 5b
            if (strlen($val) > 0) {       // regla 6
                $val = str_replace('|', '/', $val); // regla 1
                $nodo->setAttribute($key, $val);
            }
        }
    }

    /***********************************************************+******
     * ****************************************************************
     * ****************************************************************
     */

    public function facturaXmlAarray(&$strXml = false)
    {
        $sRutaArchivoXML = 'd:\\archp.xml';
        $this->aXmlDatFactura = array();
        $xml = new DOMDocument("1.0", "UTF-8");
        if ($strXml === false) {
            if (!$xml->load($sRutaArchivoXML)) return;
        } else {
            $xml->load($strXml . '.xml');
        }
        $this->xmlApdf_getDatosComprobante($xml);
        // $this->xmlApdf_getDatosTimbreFiscalDigitalSinTFD($xml);
        $this->xmlApdf_getDatosTimbreFiscalDigital($xml);
    }

    private function xmlApdf_getDatosComprobante(DomDocument &$xml)
    {
        $nodoComprobante = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/4", 'Comprobante')->item(0) ?? null;
        if ($nodoComprobante === null) {
            $nodoComprobante = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/3", 'Comprobante')->item(0) ?? null;
        }
        $this->aXmlDatFactura['version'] = $nodoComprobante->attributes->getNamedItem('Version')->nodeValue ?? '';
        if ($this->aXmlDatFactura['version'] == '') $this->aXmlDatFactura['version'] = $nodoComprobante->attributes->getNamedItem('version')->nodeValue ?? '';
        if ($this->aXmlDatFactura['version'] == '3.3')
            $this->xmlApdfVer3($nodoComprobante, $xml);
        else
            $this->xmlApdfVer4($nodoComprobante, $xml);
    }

    private function xmlApdf_getDatosTimbreFiscalDigital(DOMDocument &$xml)
    {

        $nodotimbre = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/TimbreFiscalDigital", 'TimbreFiscalDigital')->item(0);
        $this->aXmlDatFactura['UUID'] = $nodotimbre->attributes->getNamedItem('UUID')->nodeValue ?? '';
        $this->aXmlDatFactura['FechaTimbrado'] = $nodotimbre->attributes->getNamedItem('FechaTimbrado')->nodeValue ?? '';
        $this->aXmlDatFactura['RfcProvCertif'] = $nodotimbre->attributes->getNamedItem('RfcProvCertif')->nodeValue ?? '';
        $this->aXmlDatFactura['SelloCFD'] = $nodotimbre->attributes->getNamedItem('SelloCFD')->nodeValue ?? '';
        $this->aXmlDatFactura['NoCertificadoSAT'] = $nodotimbre->attributes->getNamedItem('NoCertificadoSAT')->nodeValue ?? '';
        $this->aXmlDatFactura['SelloSAT'] = $nodotimbre->attributes->getNamedItem('SelloSAT')->nodeValue ?? '';
        $xml->formatOutput = false;
        $sCadenaNodoTimbre = $xml->saveXML($nodotimbre);
        $cadOriginal = $this->xmlApdfExtraeCadenaOriginalTFD($sCadenaNodoTimbre);
        $this->aXmlDatFactura['cadOriTimbre'] = $cadOriginal;
        $this->aXmlDatFactura['QR'] = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?' .
            'id=' . $this->aXmlDatFactura['UUID'] .
            '&re=' . $this->aXmlDatFactura['emiRfc'] .
            '&rr=' . $this->aXmlDatFactura['recRfc'] .
            '&tt=' . trim(sprintf("%25.6f", floatval($this->aXmlDatFactura['Total']))) .
            '&fe=' . (substr($this->aXmlDatFactura['SelloCFD'], -8));
    }

    private function xmlApdf_getDatosTimbreFiscalDigitalSinTFD(DOMDocument &$xml)
    {
        function guidv4($data = null)
        {
            $data = $data ?? random_bytes(16);

            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }


        $nodocompro = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/4", 'Comprobante')->item(0);
        $this->aXmlDatFactura['UUID'] = guidv4();
        $this->aXmlDatFactura['FechaTimbrado'] = $nodocompro->attributes->getNamedItem('Fecha')->nodeValue ?? '';
        $this->aXmlDatFactura['RfcProvCertif'] = 'KLGA881203K83';
        $this->aXmlDatFactura['SelloCFD'] = $nodocompro->attributes->getNamedItem('Sello')->nodeValue ?? '';
        $this->aXmlDatFactura['NoCertificadoSAT'] = $nodocompro->attributes->getNamedItem('NoCertificado')->nodeValue ?? '';
        $this->aXmlDatFactura['SelloSAT'] = $nodocompro->attributes->getNamedItem('Sello')->nodeValue ?? '';
        $xml->formatOutput = false;
        $sCadenaNodoTimbre = $xml->saveXML($nodocompro);
        $cadOriginal = $this->xmlApdfExtraeCadenaOriginalTFD($sCadenaNodoTimbre);
        $this->aXmlDatFactura['cadOriTimbre'] = $cadOriginal;
        $this->aXmlDatFactura['QR'] = 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?' .
            'id=' . $this->aXmlDatFactura['UUID'] .
            '&re=' . $this->aXmlDatFactura['emiRfc'] .
            '&rr=' . $this->aXmlDatFactura['recRfc'] .
            '&tt=' . trim(sprintf("%25.6f", floatval($this->aXmlDatFactura['Total']))) .
            '&fe=' . (substr($this->aXmlDatFactura['SelloCFD'], -8));
    }


    private function xmlApdfVer3(DOMNode &$nodoComprobante, DOMDocument &$xml)
    {
        $arrImpuesto = [
            '001' => 'ISR',
            '002' => 'IVA',
            '003' => 'IEPS'
        ];
        $this->leeAtributo($nodoComprobante, 'Sello');
        $this->leeAtributo($nodoComprobante, 'Serie');
        $this->leeAtributo($nodoComprobante, 'Folio');
        $this->leeAtributo($nodoComprobante, 'Fecha');
        $this->leeAtributo($nodoComprobante, 'TipoDeComprobante');
        $this->leeAtributo($nodoComprobante, 'FormaPago');
        $this->leeAtributo($nodoComprobante, 'CondicionesDePago');
        $this->leeAtributo($nodoComprobante, 'MetodoPago');
        $this->leeAtributo($nodoComprobante, 'NoCertificado');
        $this->leeAtributo($nodoComprobante, 'SubTotal');
        $this->leeAtributo($nodoComprobante, 'Descuento');
        $this->leeAtributo($nodoComprobante, 'TipoCambio');
        $this->leeAtributo($nodoComprobante, 'Moneda');
        $this->leeAtributo($nodoComprobante, 'Total');
        $this->leeAtributo($nodoComprobante, 'LugarExpedicion');
        $nodoEmisor = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/3", 'Emisor')->item(0) ?? null;
        $this->leeAtributo($nodoEmisor, 'Rfc', 'emiRfc');
        $this->leeAtributo($nodoEmisor, 'Nombre', 'emiNombre');
        $this->leeAtributo($nodoEmisor, 'RegimenFiscal', 'emiRegimenFiscal');
        $nodoReceptor = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/3", 'Receptor')->item(0) ?? null;
        $this->leeAtributo($nodoReceptor, 'UsoCFDI', 'recUsoCFDI');
        $this->leeAtributo($nodoReceptor, 'Rfc', 'recRfc');
        $this->leeAtributo($nodoReceptor, 'Nombre', 'recNombre');
        $nodos = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/3", 'Concepto') ?? null;
        $this->aXmlDatFactura['conceptos'] = [];
        $nTotalImpuestosIVA16 = 0;
        $nInd = 0;
        foreach ($nodos as $nodo) {
            $this->procesaNodoVer3($nodo, $nInd, $nTotalImpuestosIVA16, $arrImpuesto);
            $nInd++;
        }
        $this->aXmlDatFactura['impuestosIVA'] = number_format($nTotalImpuestosIVA16, 2);
    }

    private function procesaNodoVer3(DOMElement &$nodo, &$nInd, &$nTotalImpuestosIVA16, &$arrImpuesto)
    {
        $this->aXmlDatFactura['conceptos'][] = [
            $nodo->attributes->getNamedItem('ClaveProdServ')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('Cantidad')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('ClaveUnidad')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('Unidad')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('NoIdentificacion')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('Descripcion')->nodeValue ?? '',
            number_format(floatval($nodo->attributes->getNamedItem('ValorUnitario')->nodeValue ?? ''), 2),
            $nodo->attributes->getNamedItem('Descuento')->nodeValue ?? '',
            number_format(floatval($nodo->attributes->getNamedItem('Importe')->nodeValue ?? ''), 2),
            0,      // total impuestos
            []
        ];
        $nodoimpuesto = $nodo->getElementsByTagName('Traslado')->item(0);
        if ($nodoimpuesto !== null) {
            $nImpIVA = floatval($nodoimpuesto->attributes->getNamedItem('Importe')->nodeValue ?? '');
            $nTotalImpuestosIVA16 += $nImpIVA;
            $this->aXmlDatFactura['conceptos'][$nInd][10] = [
                number_format(floatval($nodoimpuesto->attributes->getNamedItem('Base')->nodeValue ?? ''), 2),
                $arrImpuesto[$nodoimpuesto->attributes->getNamedItem('Impuesto')->nodeValue ?? ''],
                $nodoimpuesto->attributes->getNamedItem('TipoFactor')->nodeValue ?? '',
                number_format(floatval($nodoimpuesto->attributes->getNamedItem('TasaOCuota')->nodeValue ?? ''), 4),
                number_format($nImpIVA, 2)
            ];
            $this->aXmlDatFactura['conceptos'][$nInd][9] = $this->aXmlDatFactura['conceptos'][$nInd][10][4];
        }
    }

    private function xmlApdfVer4(DOMNode &$nodoComprobante, DOMDocument &$xml)
    {
        $arrImpuesto = [
            '001' => 'ISR',
            '002' => 'IVA',
            '003' => 'IEPS'
        ];
        $arrMetodoPago = [
            'PUE' => 'PUE - Pago en una sola exhibición',
            'PPD' => 'PPD - Pago en parcialidades o diferido',
        ];
        $this->leeAtributo($nodoComprobante, 'Sello');
        $this->leeAtributo($nodoComprobante, 'Serie');
        $this->leeAtributo($nodoComprobante, 'Folio');
        $this->leeAtributo($nodoComprobante, 'Fecha');
        $this->leeAtributo($nodoComprobante, 'TipoDeComprobante');

        $mdlTipoPago = new TipoPagoMdl();
        $this->leeAtributo($nodoComprobante, 'FormaPago');  // select vttipopago where sTipoSAT = ''
        $regTipoPago = $mdlTipoPago->getTipoSAT($this->aXmlDatFactura['FormaPago']);
        $this->aXmlDatFactura['FormaPago'] = iconv(
            'UTF-8',
            'ISO-8859-1',
            $this->aXmlDatFactura['FormaPago'] . ' - ' . $regTipoPago['sLeyenda']
        );

        $this->leeAtributo($nodoComprobante, 'CondicionesDePago');

        $this->leeAtributo($nodoComprobante, 'MetodoPago');
        $this->aXmlDatFactura['MetodoPago'] = iconv(
            'UTF-8',
            'ISO-8859-1',
            $arrMetodoPago[$this->aXmlDatFactura['MetodoPago']]
        );

        $this->leeAtributo($nodoComprobante, 'NoCertificado');
        $this->leeAtributo($nodoComprobante, 'SubTotal');
        $this->leeAtributo($nodoComprobante, 'Descuento');
        $this->leeAtributo($nodoComprobante, 'TipoCambio');
        $this->leeAtributo($nodoComprobante, 'Moneda');
        $this->leeAtributo($nodoComprobante, 'Total');
        $this->leeAtributo($nodoComprobante, 'LugarExpedicion');
        $mdlRegimenFiscal = new RegimenFiscalMdl();
        $nodoEmisor = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/4", 'Emisor')->item(0) ?? null;
        $this->leeAtributo($nodoEmisor, 'Rfc', 'emiRfc');
        $this->leeAtributo($nodoEmisor, 'Nombre', 'emiNombre');
        $this->leeAtributo($nodoEmisor, 'RegimenFiscal', 'emiRegimenFiscal');
        $rRegFis = $mdlRegimenFiscal->getRegistros($this->aXmlDatFactura['emiRegimenFiscal']);
        $this->aXmlDatFactura['emiCadRegimenFiscal'] = $rRegFis['sDescripcion'];
        $nodoReceptor = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/4", 'Receptor')->item(0) ?? null;
        $this->leeAtributo($nodoReceptor, 'Rfc', 'recRfc');
        $this->leeAtributo($nodoReceptor, 'Nombre', 'recNombre');
        $this->leeAtributo($nodoReceptor, 'DomicilioFiscalReceptor', 'recDomicilioFiscalReceptor');
        $this->leeAtributo($nodoReceptor, 'RegimenFiscalReceptor', 'recRegimenFiscalReceptor');
        $rRegFis = $mdlRegimenFiscal->getRegistros($this->aXmlDatFactura['recRegimenFiscalReceptor']);
        $this->aXmlDatFactura['recCadRegimenFiscal'] = $rRegFis['sDescripcion'];

        $mdlUsoCFDI = new UsoCfdiMdl();
        $this->leeAtributo($nodoReceptor, 'UsoCFDI', 'recUsoCFDI');
        $rRegUso = $mdlUsoCFDI->getRegistros($this->aXmlDatFactura['recUsoCFDI']);
        $this->aXmlDatFactura['recCadUsoCFDI'] = iconv(
            'UTF-8',
            'ISO-8859-1',
            $rRegUso['sDescripcion']
        );

        $nodos = $xml->getElementsByTagNameNS("http://www.sat.gob.mx/cfd/4", 'Concepto') ?? null;
        $this->aXmlDatFactura['conceptos'] = [];
        $nTotalImpuestosIVA16 = 0;
        $nInd = 0;
        foreach ($nodos as $nodo) {
            $this->procesaNodoVer4($nodo, $nInd, $nTotalImpuestosIVA16, $arrImpuesto);
            $nInd++;
        }
        $this->aXmlDatFactura['impuestosIVA'] = number_format($nTotalImpuestosIVA16, 2);
    }

    private function procesaNodoVer4(DOMElement &$nodo, &$nInd, &$nTotalImpuestosIVA16, &$arrImpuesto)
    {
        $this->aXmlDatFactura['conceptos'][] = [
            $nodo->attributes->getNamedItem('ClaveProdServ')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('Cantidad')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('ClaveUnidad')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('Unidad')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('NoIdentificacion')->nodeValue ?? '',
            $nodo->attributes->getNamedItem('Descripcion')->nodeValue ?? '',
            number_format(floatval($nodo->attributes->getNamedItem('ValorUnitario')->nodeValue ?? ''), 2),
            $nodo->attributes->getNamedItem('Descuento')->nodeValue ?? '',
            number_format(floatval($nodo->attributes->getNamedItem('Importe')->nodeValue ?? ''), 2),
            0,      // total impuestos
            []
        ];
        $nodoTraslado = $nodo->getElementsByTagName('Traslado')->item(0);
        $nImpIVA = floatval($nodoTraslado->attributes->getNamedItem('Importe')->nodeValue ?? '');
        $nTotalImpuestosIVA16 += $nImpIVA;
        $this->aXmlDatFactura['conceptos'][$nInd][10] = [
            number_format(floatval($nodoTraslado->attributes->getNamedItem('Base')->nodeValue ?? ''), 2),
            $arrImpuesto[$nodoTraslado->attributes->getNamedItem('Impuesto')->nodeValue ?? ''],
            $nodoTraslado->attributes->getNamedItem('TipoFactor')->nodeValue ?? '',
            number_format(floatval($nodoTraslado->attributes->getNamedItem('TasaOCuota')->nodeValue ?? ''), 4),
            number_format($nImpIVA, 2)
        ];
        $this->aXmlDatFactura['conceptos'][$nInd][9] = $this->aXmlDatFactura['conceptos'][$nInd][10][4];
    }

    private function leeAtributo(DOMNode $nodo, $sNodo, $sNodoNombreNuevo = false, &$otroArray = null)
    {
        if ($otroArray === null) {
            $this->aXmlDatFactura[($sNodoNombreNuevo ? $sNodoNombreNuevo : $sNodo)] = $nodo->attributes->getNamedItem($sNodo)->nodeValue ?? '';
        } else {
            $otroArray[($sNodoNombreNuevo ? $sNodoNombreNuevo : $sNodo)] = $nodo->attributes->getNamedItem($sNodo)->nodeValue ?? '';
        }
    }

    private function xmlApdfExtraeCadenaOriginalTFD(string &$sCadenaNodoTimbre)
    {
        if (strpos($sCadenaNodoTimbre, '/XMLSchema-instance') === false) {
            $pos = strpos($sCadenaNodoTimbre, 'xsi:schemaLocation');
            $sCadenaNodoTimbre = substr_replace(
                $sCadenaNodoTimbre,
                'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ',
                $pos,
                0
            );
        }

        $xmlTimbre = new DOMdocument("1.0", "utf-8");
        $xmlTimbre->loadXML('<?xml version="1.0" encoding="utf-8"?>' . $sCadenaNodoTimbre);

        $xmlXsl = new DOMDocument("1.0", "UTF-8");
        $xmlXsl->load('assets/facturaV4/cadenaoriginal_TFD_1_1.xslt');
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet($xmlXsl);

        return $xsl->transformToXML($xmlTimbre);
    }

    public function enviaCorreo()
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            // $mail->Host       = 'smtp-relay.sendinblue.com';                     //Set the SMTP server to send through
            // $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            // $mail->Username   = 'cigfridor@gmail.com';                     //SMTP username
            // $mail->Password   = 'bI6tEDfcA3g2JCMh';                               //SMTP password
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            // $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            $mail->Host       = 'smtp.titan.email';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'servicios@ferromat.mx';                     //SMTP username
            $mail->Password   = 'ecSKDlmqTg';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`


            //Recipients
            $mail->setFrom('servicios@ferromat.mx', 'FERROMAT');
            // $mail->setFrom('cigfridor@gmail.com', 'FERROMAT');
            $mail->addAddress($this->datosParaFactura['email']);     //Add a recipient
            // $mail->addAddress($this->datosParaFactura['emailSuc']);     //Add a recipient
            // $mail->addAddress('nestordaniel@hotmail.com');     //Add a recipient
            // $mail->addAddress('cigfridor@hotmail.com');     //Add a recipient
            // $mail->addAddress('cigfridor@gmail.com');       //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            $mail->addAttachment($this->archivoDestino . '.xml', $this->datosParaFactura['UUID'] . '.xml');         //Add attachments
            $mail->addAttachment($this->archivoDestino . '.pdf', $this->datosParaFactura['UUID'] . '.pdf');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Envio de Factura';
            $mail->Body    = 'Le proporcionamos su CFDI solicitado. <br> Buen dia!!';
            $mail->AltBody = 'Le proporcionamos su CFDI solicitado. Buen dia!!';

            $mail->send();
            return true;
            // echo 'Mensaje enviado';
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }

    private function descargaComprobante($uuid)
    {
        if ($this->conectaParaTimbrar() === false) return false;
        // lee datos de la sucursal
        $rSuc = $this->mdlSucursal->leeFolio($this->nIdSucursal, false);
        if ($rSuc === false) {
            $this->aError[] = 'Posiblemente otro usuario está usando el proceso de facturacion, intente de nuevo más tarde. ';
            return false;
        };
        $this->idPAC = $rSuc['sIdPAC'];
        $this->pswPAC = $rSuc['sPswPAC'];
    }

    /***
     * -- funcion de proceso para timbrar facturas
     * 
     * Se solicita el listado de facturas a timbrar. (retorno: array idFacturas, string nombreArchivoMensajes)
     * Se activa el proceso para leer el estado en nombreArchivoMensajes cada x segundos.
     * Por cada factura o cada N facturas:
     * Se solicita timbrar el idFactura (retorno: factura timbrada. se desbloquea bandera para pasar a la siguiente factura)
     * Al solicitar se bloquea la bandera para no solicitar otra factura haste que se termine de
     *    procesar la actual
     * 
     * 
     *  
     * 
     * -- proceso para consultar comprobantes
     * 
     * 
     * 
     */
}
