<?php
$bPrint = isset($printerMode) ? 'true' : 'false';
$aKeys = [
    'viaje' => ['nIdViaje', 'dViaje',],
];
$operacion = 'viaje';
$sKey = $aKeys[$operacion][0];
$sMov = $aKeys[$operacion][1];
$containerClass = "container-fluid h-100";
$tblClass = "table table-striped table-hover table-sm imprimir";
$borderClass = "col bg-light px-4 pt-3 border rounded";
if (isset($printerMode)) {
    $containerClass = "overflow-auto container-fluid imprimir";
    $tblClass = "table table-sm";
    $borderClass = "col px-4 pt-3";
}

function imprimeCabeceroViaje($titulo, $nFolio, $sChofer, &$aInfoSis)
{
?>
    <div class="row mt-1 fw-normal border-bottom ">
        <div class="col align-text-top text-center pb-2">
            <img src="<?= base_url() ?>/assets/img/<?= $aInfoSis['bannernavbrand'] ?>" alt="" height="90" class="float-start">
            <h5 style="line-height:30px;margin:0;padding:0;margin-left:140px;"><?= $titulo ?></h5>
            <div class="me-4 fs-8 text-center" style="height:30px;margin-left:140px;">
                Folio Viaje: <strong><?= sprintf('%07d', intval($nFolio)) ?></strong>
                <span class="ps-4"></span>
                Chofer: <strong><?= $sChofer ?></strong>
            </div>
            <div class="me-4 fs-8 text-center" style="height:30px;margin-left:140px;">
                Fecha y Hora: <strong><?= (new DateTime())->format('d/m/Y H:i:s') ?></strong>
            </div>
        </div>
    </div>
<?php
}

?>

<?php generaCssImpresion(); ?>

<?php imprimeCabeceroViaje('ENVIOS A CLIENTES (COPIA CHOFER)', $nIdViaje, $nomChofer, $aInfoSis); ?>

<style>
    #detailTabla tr {
        line-height: 12px;
    }

    #detailTabla td {
        border: none;
        font-size: .85rem;
    }
    tr.pieNota {
        line-height: 16px!important;
    }
</style>
<table class="<?= $tblClass ?> ">
    <thead>
        <tr>
            <th># Envio</th>
            <th># Remision</th>
            <th>F.Entrega</th>
            <th>Enviar a</th>
            <th>Dirección</th>
            <th>Referencias</th>
        </tr>
    </thead>
    <tbody id="bodyTabla">
        <?php
        if (empty($registros)) {
            echo "<tr>";
            echo "    <td colspan='6' class='fs-5 text-center'>No hay registros</td>";
            echo "</tr>";
        } else {
            $nEnv = 0;
            foreach ($registros as $r) {
                $nKey = $r['nIdEnvio'];
                $dMov = (new DateTime($r[$sMov]))->format('d-m-Y');
                if ($nEnv <> $nKey) {

                    if ($nEnv <> 0) {
                        echo "<tr class=\"pieNota\">";
                        echo "<td colspan=\"3\">Firma de recibido: ___________________________________________<BR>";
                        echo "Observaciones: ______________________________________________________________________<BR>";
                        echo "_______________________________________________________________________________________<BR>";
                        echo "</td>";
                        echo "</tr>";
                        echo "</tbody>";
                        echo "</table>";
                        echo "</td></tr>";
                    }

                    echo "<tr>";
                    echo "    <td> $nKey </td>";
                    echo "    <td> " . $r['nFolioRemision'] . " </td>";
                    echo "    <td> $dMov </td>";
                    echo "    <td> " . $r['nIdCliente']  . " " . $r['sEnvEntrega'] . "</td>";
                    echo "    <td> " . $r['sEnvDireccion'] . " " . $r['sEnvColonia'] . "</td>";
                    echo "    <td> " . $r['sEnvReferencia'] . "</td>";
                    echo "</tr>";

                    echo "<tr><td></td><td colspan='5'>";
                    echo "<table class='" . $tblClass .  "'>";
                    echo "  <thead>";
                    echo "    <tr>";
                    echo "        <th>Id Art</th>";
                    echo "        <th>Artículo</th>";
                    echo "        <th>Entregar</th>";
                    echo "    </tr>";
                    echo "  </thead>";
                    echo "  <tbody id='detailTabla'>";

                    $nEnv = $nKey;
                }
                echo "<tr>";
                echo "    <td>" . $r['nIdArticulo'] . "</td>";
                echo "    <td>" . $r['sArticulo'] . "</td>";
                echo "    <td>" . $r['fPorRecibir'] . "</td>";
                echo "</tr>";
            }
            echo "<tr class=\"pieNota\">";
            echo "<td colspan=\"3\">Firma de recibido: ___________________________________________<BR>";
            echo "Observaciones: ___________________________________________________________________<BR>";
            echo "___________________________________________________________________________________<BR>";
            echo "</td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";
            echo "</td></tr>";
        }
        ?>
    </tbody>
</table>

<p class="d-none page-break d-sm-block"></p>
<?php imprimeCabeceroViaje('ENVIOS A CLIENTES (COPIA BODEGA)', $nIdViaje, $nomChofer, $aInfoSis); ?>
<table class="<?= $tblClass ?> ">
    <thead>
        <tr>
            <th># Envio</th>
            <th># Remision</th>
            <th>F.Entrega</th>
            <th>Enviar a</th>
            <th>Dirección</th>
            <th>Referencias</th>
        </tr>
    </thead>
    <tbody id="bodyTabla">
        <?php
        if (empty($registros)) {
            echo "<tr>";
            echo "    <td colspan='6' class='fs-5 text-center'>No hay registros</td>";
            echo "</tr>";
        } else {
            $nEnv = 0;
            foreach ($registros as $r) {
                $nKey = $r['nIdEnvio'];
                $dMov = (new DateTime($r[$sMov]))->format('d-m-Y');
                if ($nEnv <> $nKey) {

                    if ($nEnv <> 0) {
                        echo "</tbody>";
                        echo "</table>";
                        echo "</td></tr>";
                    }

                    echo "<tr>";
                    echo "    <td> $nKey </td>";
                    echo "    <td> " . $r['nFolioRemision'] . " </td>";
                    echo "    <td> $dMov </td>";
                    echo "    <td> " . $r['nIdCliente']  . " " . $r['sEnvEntrega'] . "</td>";
                    echo "    <td> " . $r['sEnvDireccion'] . " " . $r['sEnvColonia'] . "</td>";
                    echo "    <td> " . $r['sEnvReferencia'] . "</td>";
                    echo "</tr>";

                    echo "<tr><td></td><td colspan='5'>";
                    echo "<table class='" . $tblClass .  "'>";
                    echo "  <thead>";
                    echo "    <tr>";
                    echo "        <th>Id Art</th>";
                    echo "        <th>Artículo</th>";
                    echo "        <th>Entregar</th>";
                    echo "    </tr>";
                    echo "  </thead>";
                    echo "  <tbody id='detailTabla'>";

                    $nEnv = $nKey;
                }
                echo "<tr>";
                echo "    <td>" . $r['nIdArticulo'] . "</td>";
                echo "    <td>" . $r['sArticulo'] . "</td>";
                echo "    <td>" . $r['fPorRecibir'] . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</td></tr>";
        }
        ?>
    </tbody>
</table>

<p class="d-none page-break d-sm-block"></p>
<?php imprimeCabeceroViaje('Notas de entrega (COPIA Cliente)', $nIdViaje, $nomChofer, $aInfoSis); ?>

<script type="text/javascript">
    $(document).ready(function() {
        let imprime = <?= $bPrint ?>;
        if (imprime == true) {
            $('#mainCnt').addClass('container-fluid').removeClass('container')[0].style.height = 'auto';
            $('#mainCnt > div').removeClass('row');
            $('footer').remove()
            window.print();
            //window.location.replace('../');
            history.back();
        }
    });
</script>
