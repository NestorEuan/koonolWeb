<?php

namespace App\Models\Ventas;

use CodeIgniter\Model;

class EsCreditoPagoMdl extends Model
{
  protected $table = 'vtescreditopago';

  protected $allowedFields = ['nIdEScaja', 'nIdCreditoPago'];

  protected $primaryKey = 'nIdEScaja';

  protected $useTimestamps = false;

  protected $useSoftDeletes = false;

  protected $createdField = '';

  protected $deletedField = '';

  protected $updatedField = '';

  protected $useAutoIncrement = false;

  
}