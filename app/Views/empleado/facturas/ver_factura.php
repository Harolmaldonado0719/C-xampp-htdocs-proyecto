
<?php
// Asegurarse de que las variables necesarias están definidas
$pageTitle = $pageTitle ?? 'Detalle de Factura';
$active_page = $active_page ?? 'mis_facturas_empleado';
$factura = $factura ?? null; 
$contador_notificaciones_no_leidas = $contador_notificaciones_no_leidas ?? 0;

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currencySymbol = '€', $decimalSeparator = ',', $thousandSeparator = '.') { 
        if (class_exists('NumberFormatter')) {
            // Usar NumberFormatter si la extensión intl está habilitada
            $fmt = new NumberFormatter('es_ES', NumberFormatter::CURRENCY);
            // Para asegurar que usa el símbolo correcto si la configuración regional no es la esperada para EUR
            if ($currencySymbol === '€') { // Asumiendo que EUR es el objetivo principal
                 return $fmt->formatCurrency($amount, 'EUR');
            }
            // Para otros símbolos, podría necesitarse lógica adicional o confiar en la configuración regional
            return $fmt->formatCurrency($amount, 'EUR'); // Por defecto a EUR si no se especifica
        } else {
            // Fallback a number_format si intl no está disponible
            // Formato simple: 1.234,56 €
            return number_format($amount, 2, $decimalSeparator, $thousandSeparator) . ' ' . $currencySymbol;
        }
    }
}

$estados_factura_posibles = ['Pendiente', 'Pagada', 'Anulada']; 

?>

<div class="container mt-5 mb-5">
    <div class="row d-flex justify-content-center">
        <div class="col-md-10 col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        Detalle de Factura: <?= htmlspecialchars($factura['numero_factura'] ?? 'N/A') ?>
                        <a href="<?= BASE_URL . 'empleado/facturas' ?>" class="btn btn-light btn-sm float-end">Volver a Mis Facturas</a>
                    </h4>
                </div>

                <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <?= htmlspecialchars($_SESSION['mensaje_exito_global']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_exito_global']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['mensaje_error_global'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                        <?= htmlspecialchars($_SESSION['mensaje_error_global']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_error_global']); ?>
                <?php endif; ?>
                
                <?php if ($factura): ?>
                    <div class="card-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Nº Factura:</strong> <?= htmlspecialchars($factura['numero_factura'] ?? 'N/A') ?></p>
                                <p><strong>ID Factura (interno):</strong> <?= htmlspecialchars($factura['id'] ?? 'N/A') ?></p>
                                <p><strong>Fecha Emisión:</strong> <?= htmlspecialchars(isset($factura['fecha_emision']) ? date('d/m/Y H:i', strtotime($factura['fecha_emision'])) : 'N/A') ?></p>
                                <p><strong>Estado Actual:</strong> 
                                    <?php 
                                    $estado_actual = $factura['estado_factura'] ?? 'Desconocido';
                                    $badge_class_actual = 'bg-secondary';
                                    switch (strtolower($estado_actual)) {
                                        case 'pagada': $badge_class_actual = 'bg-success'; break;
                                        case 'pendiente': $badge_class_actual = 'bg-warning text-dark'; break;
                                        case 'anulada': $badge_class_actual = 'bg-danger'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $badge_class_actual ?> fs-6"><?= htmlspecialchars(ucfirst($estado_actual)) ?></span>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p><strong>Monto Total:</strong> <span class="fs-5 fw-bold text-primary"><?= htmlspecialchars(isset($factura['monto_total']) ? formatCurrency((float)$factura['monto_total']) : 'N/A') ?></span></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>Cliente:</h5>
                                <p><?= htmlspecialchars(($factura['cliente_nombre'] ?? '') . ' ' . ($factura['cliente_apellido'] ?? '')) ?></p>
                                <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($factura['cliente_email'] ?? 'No disponible') ?></p>
                                <p><i class="fas fa-phone me-2"></i><?= htmlspecialchars($factura['cliente_telefono'] ?? 'No disponible') ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Atendido por (Empleado):</h5>
                                <p><?= htmlspecialchars(($factura['empleado_nombre'] ?? '') . ' ' . ($factura['empleado_apellido'] ?? '')) ?></p>
                            </div>
                        </div>
                        <hr>
                        <h5>Detalles del Servicio</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Servicio en Factura</th>
                                        <th>Fecha Cita Original</th>
                                        <th class="text-end">Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= htmlspecialchars($factura['servicio_nombre_en_factura'] ?? 'Servicio no especificado') ?></td>
                                        <td><?= htmlspecialchars(isset($factura['fecha_hora_cita']) ? date('d/m/Y H:i', strtotime($factura['fecha_hora_cita'])) : 'N/A') ?></td>
                                        <td class="text-end"><?= htmlspecialchars(isset($factura['monto_total']) ? formatCurrency((float)$factura['monto_total']) : 'N/A') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($factura['notas_cliente'])): ?>
                        <hr>
                        <div class="mt-3">
                            <h5>Notas del Cliente (de la cita original):</h5>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($factura['notas_cliente'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <h5 class="mb-3">Actualizar Estado de la Factura</h5>
                                <form action="<?= BASE_URL . 'empleado/facturas/actualizar-estado/' . htmlspecialchars($factura['id'] ?? '') ?>" method="POST">
                                    <div class="mb-3">
                                        <label for="estado_factura" class="form-label">Nuevo Estado:</label>
                                        <select class="form-select" id="estado_factura" name="estado_factura" required>
                                            <?php foreach ($estados_factura_posibles as $estado_opcion): ?>
                                                <option value="<?= htmlspecialchars($estado_opcion) ?>" <?= (isset($factura['estado_factura']) && strtolower($factura['estado_factura']) == strtolower($estado_opcion)) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars(ucfirst($estado_opcion)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-save me-2"></i>Guardar Nuevo Estado
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                <?php else: ?>
                    <div class="card-body">
                        <div class="alert alert-warning text-center" role="alert">
                            No se pudo cargar la información de la factura o no tienes permiso para verla.
                        </div>
                    </div>
                <?php endif; ?>
                <div class="card-footer text-muted text-center">
                    Clip Techs System - <?= date('Y') ?>
                </div>
            </div>
        </div>
    </div>
</div>