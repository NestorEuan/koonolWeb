<?php

use Config\Services;

if (!function_exists('generaCampoTexto')) {
    function generaCampoTexto(
        $campo = '',
        $error = false,
        $tipo = 'text',
        $registro = null,
        $modo = '',
        $class = '',
        $attr = '',
        $regOpciones = null,
        $opcion = 'sDes',
        $opcionInvalida = ''
    ) {
        if ($error === false) {
            if ($tipo == 'checkbox') {
                echo '<input type="' . $tipo . '" class="form-check-input ' . $class . '" id="' . $campo . '" name="' . $campo
                    . '" ' . ($modo == 'B' ? 'readonly ' : ' ') .  $attr . ' '
                    . (esc($registro[$campo] ?? '') == $opcion ? 'checked' : '') . '>';
            } elseif ($tipo == 'textarea') {
                echo '<textarea class="form-control ' . $class . '" id="' . $campo . '" name="' .
                    $campo . '" rows="2" ' . ($modo == 'B' ? 'readonly ' : ' ') . $attr . ' >' .
                    esc($registro[$campo] ?? '') . '</textarea>';
            } elseif ($tipo == 'select') {
                $valOpcion = $registro[$campo] ?? '';
                echo '<select name="' . $campo . '" id="' . $campo . '" class="form-select ' . $class . '" ' .
                    ($modo == 'B' ? 'disabled ' : ' ') .  $attr . ' >';
                echo '<option value="-1"' .
                    (($valOpcion == '' || $valOpcion == '-1' || $valOpcion == $opcionInvalida) ? ' selected ' : '') .
                    '>Seleccione una opción</option>';
                // $registrodet se pasa siempre el id, y opcion
                foreach ($regOpciones as $r) {
                    echo '<option value="' . esc($r[$campo]) . '" ' . ($r[$campo] == $valOpcion ? 'selected' : '') . '>' .
                        esc($r[$opcion]) .
                        '</option>';
                }
                echo '</select>';
            } else {
                echo '<input type="' . $tipo . '" class="form-control ' . $class . '" id="' . $campo . '" name="' . $campo
                    . '" value="' . esc($registro[$campo] ?? '') . '" ' . ($modo == 'B' ? 'readonly ' : ' ') .  $attr . ' >';
            }
        } else {
            if (isset($error[$campo])) {
                if ($tipo == 'checkbox') {
                    echo '<input type="' . $tipo . '" class="form-check-input is-invalid ' . $class . '" id="' . $campo . '" name="' . $campo
                        . '" aria-describedby="' . $campo . 'FB" ' . $attr . ' '
                        . (set_value($campo) == '' ? '' : 'checked') . '>';
                } elseif ($tipo == 'textarea') {
                    echo '<textarea class="form-control is-invalid ' . $class . '" id="' . $campo . '" name="' .
                        $campo . '" aria-describedby="' . $campo . 'FB" rows="2" ' . $attr . ' >' .
                        set_value($campo) . '</textarea>';
                } elseif ($tipo == 'select') {
                    $valOpcion = set_value($campo);
                    echo '<select name="' . $campo . '" id="' . $campo . '" class="form-select is-invalid ' . $class . '" ' .
                        'aria-describedby="' . $campo . 'FB" '  .  $attr . '>';
                    echo '<option value="-1"' .
                        (($valOpcion == '' || $valOpcion == '-1' || $valOpcion == $opcionInvalida) ? ' selected ' : '') .
                        '>Seleccione una opción</option>';
                    foreach ($regOpciones as $r) {
                        echo '<option value="' . esc($r[$campo]) . '" ' . (esc($r[$campo]) == $valOpcion ? 'selected' : '') . '>' .
                            esc($r[$opcion]) .
                            '</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<input type="' . $tipo . '" class="form-control is-invalid ' . $class . '" id="' . $campo . '" name="' . $campo
                        . '" aria-describedby="' . $campo . 'FB" value="' . set_value($campo) . '" ' . $attr . ' >';
                }
                echo '<div id="' . $campo . 'FB" class="invalid-feedback">' . $error[$campo] . '</div>';
            } else {
                if ($tipo == 'checkbox') {
                    echo '<input type="' . $tipo . '" class="form-check-input is-valid ' . $class . '" id="' . $campo . '" name="' . $campo
                        . '" ' . $attr . ' ' . (set_value($campo) == '' ? '' : 'checked') . '>';
                } elseif ($tipo == 'textarea') {
                    echo '<textarea class="form-control is-valid ' . $class . '" id="' . $campo . '" name="' .
                        $campo . '" rows="2" ' . $attr . ' >' .
                        set_value($campo) . '</textarea>';
                } elseif ($tipo == 'select') {
                    $valOpcion = set_value($campo);
                    echo '<select name="' . $campo . '" id="' . $campo . '" class="form-select is-valid ' . $class . '" ' .
                        $attr . '>';
                    echo '<option value="-1"' .
                        (($valOpcion == '' || $valOpcion == '-1') ? ' selected ' : '') .
                        '>Seleccione una opción</option>';
                    foreach ($regOpciones as $r) {
                        echo '<option value="' . esc($r[$campo]) . '" ' . (esc($r[$campo]) == $valOpcion ? 'selected' : '') . '>' .
                            esc($r[$opcion]) .
                            '</option>';
                    }
                    echo '</select>';
                } else {
                    echo '<input type="' . $tipo . '" class="form-control is-valid ' . $class . '" id="' . $campo . '" name="' .
                        $campo . '" value="' . set_value($campo) . '" ' . $attr . ' >';
                }
            }
        }
    }
}

if (!function_exists('generaModalGeneral')) {
    function generaModalGeneral($id = 'frmModal', $ancho = '', $conBackdrop = true)
    { ?>
        <div class="modal fade" id="<?= $id ?>" tabindex="-1" <?= $conBackdrop ? 'data-bs-backdrop="static"' : '' ?>>
            <div class="modal-dialog <?= $ancho ?>">
                <div class="modal-content">
                    <div class="modal-body"></div>
                    <div id="<?= $id . 'wAlert' ?>">
                        <div class="alert alert-danger alert-dismissible position-absolute" style="display:none; top:5px; left:5px;" role="alert">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
}

if (!function_exists('generaCssImpresion')) {
    function generaCssImpresion()
    { ?>
        <style>
            /*div.imprimir * {
                /*font-family: Arial, Helvetica, sans-serif;
                /*font-size: 8px;
            }*/

            div.imprimir table {
                border: none;
                width: 100%;
                margin: 0 auto;
            }

            div.imprimir .textTitulo1 {
                font-weight: bold;
                font-size: 8px;
            }

            div.imprimir .textNormal10 {
                font-weight: normal;
                font-size: 10px;
            }

            div.imprimir .textNormal12 {
                font-weight: normal;
                font-size: 12px;
            }

            div.imprimir .textNormal14 {
                font-weight: normal;
                font-size: 14px;
            }

            div.imprimir .fs-10 {
                font-weight: normal;
                font-size: .5rem !important;
            }

            div.imprimir .fs-9 {
                font-weight: normal;
                font-size: .625rem !important;
            }

            div.imprimir .fs-8 {
                font-weight: normal;
                font-size: .75rem !important;
            }

            div.imprimir .fs-7 {
                font-weight: normal;
                font-size: .875rem !important;
            }

            .page-break {
                position: relative;
            }

            @media (min-width: 576px) {

                div.imprimir .fs-sm-10 {
                    font-weight: normal;
                    font-size: .5rem !important;
                }

                div.imprimir .fs-sm-9 {
                    font-weight: normal;
                    font-size: .625rem !important;
                }

                div.imprimir .fs-sm-8 {
                    font-weight: normal;
                    font-size: .75rem !important;
                }

                div.imprimir .fs-sm-7 {
                    font-weight: normal;
                    font-size: .875rem !important;
                }

                .page-break {
                    display: block;
                    break-before: always;
                    page-break-before: always;
                    position: relative;
                }
            }

            div.imprimir .head1 {
                border-bottom: 1px solid #000;
            }

            #cabDet>th {
                border: 1px solid #000;
            }
        </style>
<?php
    }
}

if (!function_exists('initFechasFiltro')) {
    /**
     * Inicializa un rango de fechas. 
     * 
     * - Si $tipoInit no se indica, es null o '', el rango devuelto es de 60 dias hacia atras<br>
     * - Si $tipoInit es 'D' el rango devuelto es del dia actual.
     * - Si $tipoInit es 'M' el rango devuelto es del mes en curso.
     * - Si $tipoInit es un numero 'NN' el rango devuelto es de 'NN' dias hacia atras.
     * - Si la fecha final no se establece, entonces el rango es de 30 dias a partir de la fecha
     * inicial dada. 
     * 
     * @param string $fi Fecha inicial del rango en formato 'aaaa-mm-dd'
     * @param string $ff Fecha final del rango en formato 'aaaa-mm-dd'
     * @param string $tipoInit indica como se inicializa el rango.
     * 
     * @return array Devuelve el arreglo representando el rango de fechas: [Datetime fechaInicial, Datetime fechaFinal]
     */
    function initFechasFiltro($fi = null, $ff = null, $tipoInit = null)
    {
        if ($fi == null || $fi == '') {
            $fi = null;
        } else {
            $fi = new Datetime($fi);
        }
        if ($ff == null || $ff == '') {
            $ff = null;
        } else {
            $ff = new Datetime($ff);
        }
        if ($fi === null) {
            $ff = new DateTime();
            $fi = clone $ff;
            if ($tipoInit === 'M') {
                $fi->modify('first day of');
            } elseif ($tipoInit === 'D') {
                // el mismo
            } else {
                $nDias = $tipoInit === null ? '60' : $tipoInit;
                $fi->sub(new DateInterval('P' . $nDias . 'D'));
            }
        } else {
            if ($ff == null) {
                $ff = clone $fi;
                $ff->add(new DateInterval('P1M'));
            }
        }
        return [$fi, $ff];
    }
}

if (!function_exists('set_value')) {
    /**
     * Form Value
     *
     * Grabs a value from the POST array for the specified field so you can
     * re-populate an input field or textarea
     *
     * @param string          $field      Field name
     * @param string|string[] $default    Default value
     * @param bool            $htmlEscape Whether to escape HTML special characters or not
     *
     * @return string|string[]
     */
    function set_value(string $field, $default = '', bool $htmlEscape = true)
    {
        $request = Services::request();

        // Try any old input data we may have first
        $value = $request->getOldInput($field);

        if ($value === null) {
            $value = $request->getPostGet($field) ?? $default;
        }

        return ($htmlEscape) ? esc($value) : $value;
    }
}
