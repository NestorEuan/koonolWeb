<?php

namespace App\Models\Almacen;

use CodeIgniter\Model;

class InventarioMdl extends Model
{
    protected $table = 'alinventario';

    protected $allowedFields = ['nIdSucursal', 'nIdArticulo', 'fExistencia', 'fComprometido', 'fSobreComprometido'];

    protected $primaryKey = 'nIdInventario';

    protected $useTimestamps = true;

    protected $useSoftDeletes = true;

    protected $createdField = 'dtAlta';

    protected $deletedField = 'dtBaja';

    protected $updatedField = '';

    public function updtArticuloSucursalInventario($idSuc, $idArt, $fCant, $fComprometido = 0)
    {
        //
        $invArt = [];
        $invArt = [
            'nIdSucursal' => $idSuc,
            'nIdArticulo' => $idArt,
            'fExistencia' => $fCant,
        ];
        $invartsucursal = $this->where(['nIdSucursal' => $idSuc, 'nIdArticulo' => $idArt])->first();
        if (!empty($invartsucursal)) {
            $invArt['nIdInventario'] = $invartsucursal['nIdInventario'];
            $invArt['fExistencia'] = $invartsucursal['fExistencia'] + $fCant;
            $invArt['fComprometido'] = $invartsucursal['fComprometido'] + $fComprometido;
            if ($fCant > 0) {
                if ($invartsucursal['fSobreComprometido'] > 0) {
                    $fAComprometer = $fCant;
                    if ($fCant > $invartsucursal['fSobreComprometido'])
                        $fAComprometer = $invartsucursal['fSobreComprometido'];
                    $invArt['fSobreComprometido'] = $invartsucursal['fSobreComprometido'] - $fAComprometer;
                    $invArt['fComprometido'] = $invartsucursal['fComprometido'] + $fAComprometer;
                }
            }
        }
        $this->save($invArt);
    }

    public function getArticuloSucursal($idArticulo, $idSucursal, $inv = false, $bloqueaRegistro = false)
    {
        $this->where([
            'nIdArticulo' => $idArticulo,
            'nIdSucursal' => $idSucursal . ($bloqueaRegistro ? ' for update' : '')
        ], null, false);
        if ($inv) $this->select('(fExistencia - IFNULL(fComprometido, 0.000)) AS disponible, ' .
            'fExistencia, fSobreComprometido, fComprometido');
        return $this->builder()->get()->getFirstRow('array');
    }

    public function getRegistros($idArticulo = false, $idSucursal = false)
    {
        $this->select('alinventario.*, b.sDescripcion as cNomSuc, a.sDescripcion')
            ->join('alsucursal as b', 'b.nIdSucursal = alinventario.nIdSucursal')
            ->join('alarticulo a', 'a.nIdArticulo = alinventario.nIdArticulo');
        if ($idArticulo === false) {
            if ($idSucursal === false) {
                return $this
                    ->orderBy('alinventario.nIdSucursal', 'ASC')
                    ->orderBy('alinventario.nIdArticulo', 'ASC')
                    ->findAll();
            } else {
                return $this->orderBy('alinventario.nIdArticulo', 'ASC')->where(['alinventario.IdSucursal' => $idSucursal])->findAll();
            }
        } else {
            $a['alinventario.nIdArticulo'] = $idArticulo;
            if ($idSucursal !== false) {
                $a['alinventario.nIdSucursal'] = $idSucursal;
                return $this->where($a)->findAll();
            }
        }
        return $this->orderBy('alinventario.nIdSucursal', 'ASC')->where(['alinventario.nIdArticulo' =>  $idArticulo])->findAll();
    }

    public function getRegistrosInv($idArt, $idSuc)
    {
        $this->select(
            'SUM(IF(`alinventario`.`nIdSucursal` = ' . $idSuc . ', `alinventario`.`fExistencia` - IFNULL(`alinventario`.`fComprometido`, 0.000), 0.000)) AS exiSuc, ' .
                'SUM(IF(`alinventario`.`nIdSucursal` <> ' . $idSuc . ', `alinventario`.`fExistencia` - IFNULL(`alinventario`.`fComprometido`, 0.000), 0.000)) AS exiOtros, ' .
                'SUM(IF(`alinventario`.`nIdSucursal` = ' . $idSuc . ', `alinventario`.`fExistencia`, 0.000)) as exiFis',
            false
        );
        return $this->where(['nIdArticulo' =>  intval($idArt)])->first();
    }

    public function getExistencias($idsuc, $paginado = false, $filtro = false)
    {
        $this->select('alinventario.*, a.sDescripcion AS nomArt, a.sCodigo, ' .
            '(SELECT SUM(alinv.`fExistencia` - IFNULL(alinv.`fComprometido`, 0.000)) FROM alinventario alinv WHERE alinv.nIdArticulo = alinventario.nIdArticulo ) AS nGlobal', false)
            ->join('alarticulo a', 'alinventario.nIdArticulo = a.nIdArticulo', 'inner')
            ->orderBy('alinventario.nIdArticulo', 'ASC')
            ->where('nIdSucursal', $idsuc);

        if ($filtro !== false) {
            $this->like('sDescripcion', $filtro);
        }
        if ($paginado) {
            return $this->paginate($paginado);
        } else {
            return $this->findAll();
        }





        // if($soloConExi) {
        //     $this->where('fExistencia <>', 0)
        //     ->orWhere('fComprometido <>', 0)
        //     ->orWhere('fSobreComprometido <>', 0);
        // }





        // se repasa el inventario


        /*** PARA REEMPLAZAR COMILLA SIMPLE
         $b = $this->select('GROUP_CONCAT( distinct ' .
            'concat(' . 
            '\'max( case when alinventario.nIdSucursal = \', alinventario.nIdSucursal,' .
            '\' then alinventario.fExistencia else 0 end ) as \', \'\\\'Existencia \' , ' .
            ' replace( s.sDescripcion, "\'",\'"\' ), \'\\\' \' ' .
            
            '\', max( case when alinventario.nIdSucursal = \', alinventario.nIdSucursal,' .
            '\' then alinventario.fComprometido else 0 end ) as \', \'\\\'Comprometido \' , ' .
            ' replace( s.sDescripcion, "\'",\'"\' ), \'\\\' \' ' .
            
            '\', max( case when alinventario.nIdSucursal = \', alinventario.nIdSucursal,' .
            '\' then (alinventario.fExistencia - alinventario.fComprometido) else 0 end ) as \', \'\\\'Total \' , ' .
            ' replace( s.sDescripcion, "\'",\'"\' ), \'\\\' \' ' .
            
            ')' .
            ') as ss', false)
            ->join('alsucursal s', 's.nIdSucursal = alinventario.nIdSucursal')
            //->builder()->getCompiledSelect()
            ->first()
            ;
            //var_dump($b); exit;

         ***/
        // $b = $this->select("GROUP_CONCAT( distinct " .
        //     "concat(" .
        //     "'max( case when alinventario.nIdSucursal = ', alinventario.nIdSucursal, " .
        //     "' then alinventario.fExistencia else 0 end ) as ', '\"Existencia ' , " .
        //     " replace (s.sClave, '\"', \"''\"), '\"' " .

        //     ", ', max( case when alinventario.nIdSucursal = ', alinventario.nIdSucursal, " .
        //     "' then alinventario.fComprometido else 0 end ) as ', '\"Comprometido ' , " .
        //     " replace (s.sClave, '\"', \"''\"), '\"' " .

        //     ", ', max( case when alinventario.nIdSucursal = ', alinventario.nIdSucursal, " .
        //     "' then alinventario.fSobreComprometido else 0 end ) as ', '\"SobreComp ' , " .
        //     " replace (s.sClave, '\"', \"''\"), '\"' " .

        //     ", ', max( case when alinventario.nIdSucursal = ', alinventario.nIdSucursal, " .
        //     "' then (alinventario.fExistencia - alinventario.fComprometido) else 0 end ) as ', '\"Total ' , " .
        //     " replace (s.sClave, '\"', \"''\"), '\"' " .

        //     ")" .
        //     ") as ss", false)
        //     ->join('alsucursal s', 's.nIdSucursal = alinventario.nIdSucursal')
        //     // ->builder()->getCompiledSelect();
        //     ->first();

        // // var_dump($b); exit;
        // $qry = $this->select('distinct alinventario.nIdArticulo, alarticulo.sDescripcion, ' .
        //     $b['ss'], false)
        //     ->join('alarticulo', 'alarticulo.nIdArticulo = alinventario.nIdArticulo', 'left')
        //     ->groupBy(['nIdArticulo'])
        //     //->builder()->getCompiledSelect()
        // ;
        // if ($filtro !== false) {
        //     $this->like('sDescripcion', $filtro);
        // }

        // $whereArr = [];
        // if ($idArt !== false)
        //     $whereArr['nIdArticulo'] = $idArt;
        // if ($idSuc !== false)
        //     $whereArr['alinventario.nIdSucursal'] = $idSuc;

        // //var_dump($qry->getFieldNames()); exit;
        // if (empty($whereArr)) {
        //     if ($paginado) {
        //         return $qry->paginate(8);
        //     } else {
        //         return $qry->findAll();
        //     }
        //     return $qry->where($whereArr)->findAll();
        // }
    }

    public function guardaupd()
    {
        $mm = $this->builder()
            ->set('fComprometido', 10)
            ->set('fSobreComprometido', 5)
            ->where([
                'nIdSucursal' => 1,
                'nIdArticulo' => 4
            ])->where('for update', null, false)
            ->getCompiledSelect();
        return $mm;
    }
}
/***********************
 *
CREATE TABLE IF NOT EXISTS `alinventario` (
  `nIdInventario` INT NOT NULL AUTO_INCREMENT,
  `nIdSucursal` INT NULL,
  `nIdArticulo` INT NOT NULL,
  `fExistencia` DOUBLE NULL,
  `fComprometido` DOUBLE NULL,
  PRIMARY KEY (`nIdInventario`),
  INDEX `idx_sucursal` (`nIdSucursal` ASC),
  INDEX `idx_articulo` (`nIdArticulo` ASC))
ENGINE = InnoDB
 *
 ***********/
