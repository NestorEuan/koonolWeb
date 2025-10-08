<style>
    .calculadora .button {
        min-width: 50px;
        display: inline-block;
        /* background-color: white; */
        padding: 10px 0;
        text-align: center;
        cursor: pointer;
        font-weight: bolder;
    }

    .calculadora .zero {
        width: 110px;
    }
</style>
<div class="container-fluid px-0 calculadora bg-secondary pb-3" style="z-index:auto;">
    <h3 class="fs-5 fst-italic fw-bolder text-end border-bottom border-dark border-2" style="min-height: 2em;">
        Calculadora
        <button type="button" class="btn btn-close btn-sm btn-outline-light ms-4 me-1" aria-label="Close" data-bs-dismiss="modal"></button>
    </h3>
    <div class="row rounded text-dark mx-3 mb-2" style="min-height: 3em;background-color:#aaa;border: 1px solid #888;">
        <div class="operador fs-6 fw-bolder text-end" style="min-height:.5em;"></div>
        <div class="numeros fs-4 text-end"></div>
    </div>
    <div class="px-sm-5">
        <div class="row gx-0 mb-2">
            <div class="col text-center">
                <div class="button btn btn-outline-light">C</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">CE</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">back</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">+/-</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">%</div>
            </div>
        </div>
        <div class="row gx-0 mb-2">
            <div class="col text-center">
                <div class="button btn btn-outline-light">7</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">8</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">9</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">/</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">sqrt</div>
            </div>
        </div>
        <div class="row gx-0 mb-2">
            <div class="col text-center">
                <div class="button btn btn-outline-light">4</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">5</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">6</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">*</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">1/x</div>
            </div>
        </div>
        <div class="row gx-0 mb-2">
            <div class="col text-center">
                <div class="button btn btn-outline-light">1</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">2</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">3</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">-</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">pi</div>
            </div>
        </div>
        <div class="row gx-0 mb-2">
            <div class="col text-center">
                <div class="button btn btn-outline-light">.</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">0</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">+</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">=</div>
            </div>
            <div class="col text-center">
                <div class="button btn btn-outline-light">=</div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        let calculadora = {
            result: 0,
            prevEntry: 0,
            operation: null,
            currentEntry: "0",

            init: function() {
                $(".calculadora .button").on("click", function(evt) {
                    let buttonPressed = $(evt.target).html();
                    calculadora.procesaTecla(buttonPressed);
                });
                $('body').on("keydown.calculadora", calculadora.onKeyDown);
            },
            onKeyDown: function(e) {
                let t = e.which;
                let t1 = e.shiftKey;
                let codigo = "";
                if (t > 47 && t < 58) {
                    codigo = (t - 48).toString();
                } else if (t > 95 && t < 106) {
                    codigo = (t - 96).toString();
                } else {
                    switch (t) {
                        case 110:
                        case 190:
                            codigo = ".";
                            break;
                        case 107:
                            codigo = "+";
                            break;
                        case 109:
                        case 173:
                            codigo = "-";
                            break;
                        case 106:
                            codigo = "*";
                            break;
                        case 111:
                            codigo = "/";
                            break;
                        case 13:
                            codigo = "=";
                            break;
                        case 171:
                            codigo = t1 === true ? "*" : "+";
                            break;
                        case 55:
                            codigo = t1 === true ? "/" : "";
                            break;
                        case 46:
                            codigo = "C";
                            break;
                        case 8:
                            codigo = "back";
                            break;
                    }
                }
                if (codigo != '') e.preventDefault();
                calculadora.procesaTecla(codigo);
            },

            updateScreen: function(
                displayValue,
                displayOperation,
                displayPrevEntry
            ) {
                displayValue = displayValue.toString();

                $(".numeros").html(displayValue.substring(0, 10));
                if (
                    displayOperation !== null &&
                    displayPrevEntry.toString().trim() != ""
                )
                    $(".operador").html(
                        displayPrevEntry.toString().trim() +
                        "  " +
                        displayOperation
                    );
                else $(".operador").html("");
            },
            isNumber: function(value) {
                return !isNaN(value);
            },
            isOperator: function(value) {
                return (
                    value === "/" ||
                    value === "*" ||
                    value === "+" ||
                    value === "-"
                );
            },
            operate: function(a, b, operation) {
                a = parseFloat(a);
                b = parseFloat(b);
                if (operation === null) return b;
                if (operation === "+") return a + b;
                if (operation === "-") return a - b;
                if (operation === "*") return a * b;
                if (operation === "/") return a / b;
            },
            procesaTecla: function(buttonPressed) {
                if (buttonPressed === "C") {
                    calculadora.result = 0;
                    calculadora.currentEntry = "0";
                    calculadora.operation = null;
                } else if (buttonPressed === "CE") {
                    calculadora.currentEntry = "0";
                } else if (buttonPressed === "back") {
                    calculadora.currentEntry = calculadora.currentEntry.toString();
                    calculadora.currentEntry = calculadora.currentEntry.substring(
                        0,
                        calculadora.currentEntry.length - 1
                    );
                } else if (buttonPressed === "+/-") {
                    calculadora.currentEntry *= -1;
                } else if (buttonPressed === ".") {
                    calculadora.currentEntry += ".";
                } else if (calculadora.isNumber(buttonPressed)) {
                    if (calculadora.currentEntry === "0")
                        calculadora.currentEntry = buttonPressed;
                    else
                        calculadora.currentEntry =
                        calculadora.currentEntry + buttonPressed;
                } else if (calculadora.isOperator(buttonPressed)) {
                    if (calculadora.operation === null) {
                        calculadora.prevEntry = parseFloat(
                            calculadora.currentEntry
                        );
                    }
                    calculadora.operation = buttonPressed;
                    calculadora.currentEntry = "";
                } else if (buttonPressed === "%") {
                    calculadora.currentEntry =
                        calculadora.currentEntry / 100;
                } else if (buttonPressed === "sqrt") {
                    calculadora.currentEntry = Math.sqrt(
                        calculadora.currentEntry
                    );
                } else if (buttonPressed === "1/x") {
                    calculadora.currentEntry = 1 / calculadora.currentEntry;
                } else if (buttonPressed === "pi") {
                    calculadora.currentEntry = Math.PI;
                } else if (buttonPressed === "=") {
                    calculadora.currentEntry = calculadora.operate(
                        calculadora.prevEntry,
                        calculadora.currentEntry,
                        calculadora.operation
                    );
                    calculadora.operation = null;
                }

                calculadora.updateScreen(
                    calculadora.currentEntry,
                    calculadora.operation,
                    calculadora.prevEntry
                );
            },

        };
        calculadora.init();
    });
</script>