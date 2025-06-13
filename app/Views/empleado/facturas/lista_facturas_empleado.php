<?php
// Variables disponibles: $pageTitle, $active_page, $facturas (array), $contador_notificaciones_no_leidas
?>
<div class="container mt-4">
    <div class="page-header">
        <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Mis Facturas'; ?></h2>
    </div>

    <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_global'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($facturas)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Nº Factura</th>
                        <th>Fecha Emisión</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Monto Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facturas as $factura): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($factura['numero_factura'] ?? 'N/A'); ?></td>
                            <td><?php echo isset($factura['fecha_emision']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($factura['fecha_emision']))) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars(($factura['cliente_nombre'] ?? '') . ' ' . ($factura['cliente_apellido'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($factura['servicio_nombre_en_factura'] ?? 'N/A'); ?></td>
                            <td><?php echo isset($factura['monto_total']) ? number_format($factura['monto_total'], 2, ',', '.') . ' €' : 'N/A'; ?></td>
                            <td>
                                <span class="badge <?php 
                                    $estadoFactura = strtolower($factura['estado_factura'] ?? '');
                                    switch ($estadoFactura) {
                                        case 'pagada': echo 'bg-success'; break;
                                        case 'pendiente': echo 'bg-warning text-dark'; break;
                                        case 'anulada': echo 'bg-danger'; break;
                                        default: echo 'bg-secondary'; break;
                                    }
                                ?>">
                                    <?php echo htmlspecialchars(ucfirst($factura['estado_factura'] ?? 'Desconocido')); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL . 'empleado/facturas/ver/' . htmlspecialchars($factura['id'] ?? ''); ?>" class="btn btn-info btn-sm" title="Ver Detalle">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <?php /* Futuras acciones como "Marcar como Pagada" podrían ir aquí */ ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No has generado ninguna factura todavía. Las facturas se generan automáticamente cuando confirmas una cita.</div>
    <?php endif; ?>
</div>

<style>
    .page-header { margin-bottom: 20px; }
    .badge.bg-warning.text-dark { color: #212529 !important; } /* Para mejor contraste en badge amarillo */
</style>