<!DOCTYPE html>
<html lang="en" style="height:100%;">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $aInfoSis['nomempresa'] ?></title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="<?= base_url() ?>/assets/img/<?= $aInfoSis['icono'] ?>" />

    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css"> -->


    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/js/themes/default/style.min.css">

    <script src="<?= base_url() ?>/assets/js/jquery.js"> </script>
    <script src="<?= base_url() ?>/assets/js/bootstrap.bundle.min.js"> </script>
    <script src="<?= base_url() ?>/assets/js/jstree.js"> </script>

    <!-- <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script> -->

    <script>
        const baseURL = '<?= base_url() ?>';
        let miGlobal = {
            nomSucursal: '',
            entregaCampo: null,
            dameMenu: function(e) {
                $("#mainMenu ul.navbar-nav > li.nav-item > ul.dropdown-menu li > a").on("click", function() {
                    miGlobal.toggleBlockPantalla('Cargando ' + $(this).text() + ' ...');
                });
            },
            valNumero: function(e, obj, op) {
                op = op || {};
                if (op.propValorAnterior == undefined) op.propValorAnterior = 'cantAnt';
                if (op.validacionEnBlur == undefined) op.validacionEnBlur = false;
                if (op.numeroMax == undefined) op.numeroMax = false;
                if (obj[op.propValorAnterior] == undefined) obj[op.propValorAnterior] = '';
                if (e.target.value && e.target.value.trim().length > 0) {
                    let r = (op.re != undefined) ? op.re : /^\d*(?:\.\d{0,2})?$/g;
                    if (r.test(e.target.value.trim())) {
                        if(op.numeroMax !== false) e.target.value = (Number(e.target.value.trim()) > op.numeroMax ? op.numeroMax.toString() : e.target.value);
                        if (op.min != undefined) {
                            let n = parseInt(e.target.value.trim());
                            if (op.decimal != undefined) {
                                op.decimal = Math.pow(10, op.decimal);
                                n = Math.round(parseFloat(e.target.value.trim()) * op.decimal) / op.decimal; // con redondeo a 2 decimales
                            }
                            if (n >= op.min && n <= op.max) {
                                if (!op.validacionEnBlur) obj[op.propValorAnterior] = e.target.value.trim();

                            } else {
                                e.target.value = obj[op.propValorAnterior];
                            }
                        } else {
                            if (!op.validacionEnBlur) obj[op.propValorAnterior] = e.target.value.trim();
                        }
                    } else {
                        e.target.value = obj[op.propValorAnterior];
                    }
                } else {
                    if (!op.validacionEnBlur) obj[op.propValorAnterior] = '';
                    // obj[op.propValorAnterior] = '';
                }
            },

            muestraAlerta: function(mensaje, idParent, tiempoMostrado, tiempoDespliegue, callBack) {
                let bndQuitar = false;
                if (idParent === undefined) {
                    idParent = mensaje;
                    bndQuitar = true;
                } else {
                    idParent = idParent ?? ''; // es el de por defecto
                }
                idParent = '#' + idParent + 'wAlert .alert';
                tiempoDespliegue = tiempoDespliegue ?? 500;
                let jObj = $(idParent);
                if (jObj.length > 0 && bndQuitar) {
                    jObj.slideUp(300);
                    return;
                }
                jObj.html(mensaje);
                jObj.slideDown(tiempoDespliegue);
                if (tiempoMostrado) {
                    tiempoMostrado = tiempoMostrado < 1500 ? 1500 : tiempoMostrado;
                    setTimeout(() => {
                        if(callBack) callBack();
                        jObj.slideUp(300)
                    }, tiempoMostrado);
                }
            },

            toggleBlockPantalla: function(msj) {
                let je = $('#blockGral');
                je.find('span').html(msj ?? '');
                je.toggleClass('d-none');
                je.focus();
                je.click();
            },
            agregaCamposHidden: function(sSelector, sFormDestino) {
                let oF = $(sFormDestino);
                $(sSelector).each((i, el) => {
                    oF.append(
                        '<input type="hidden" name="' + el.name +
                        '" value="' + el.value + '">'
                    );
                });
            }
        };
    </script>

</head>

<body style="height:100%;">
    <header class="bg-info border-bottom border-secondary d-print-none" style="height:57px;">
        <?php
        echo view('templates/navbar');
        ?>
    </header>
    <div id="blockGral" class="d-none" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:2000;">
        <div class="d-flex justify-content-center align-items-center h-100" style="background-color:black;opacity:0.5;">
            <div class="spinner-border text-light" role="status"></div>
            <span class="text-light ms-3"></span>
        </div>
    </div>
    <div class="container overflow-auto" style="height:calc(100% - 121px);" id="mainCnt">
        <div class="row">