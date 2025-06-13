
<?php
// Variables que se esperan de EmpleadoController->generarReportes():
// $pageTitle
// $active_page
// $valores_filtros (array con 'fecha_desde', 'fecha_hasta')
// $reporte_citas_por_estado (array ['estado'] => conteo)
// $reporte_servicios_realizados (array de arrays ['servicio_nombre', 'conteo'])

$all_possible_estados_cita = [ // Para asegurar que todos los estados se muestren, incluso con conteo 0
    'Pendiente', 'Confirmada', 'Completada', 'Cancelada', 'No Asistió', 'Reprogramada' 
    // Ajusta esta lista según los estados que manejes
];

?>

<div class="container" style="max-width: 960px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
    <div class="page-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 25px;">
        <h2 style="margin-top: 0; color: #333;"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Reportes de Actividad'; ?></h2>
    </div>

    <?php if (isset($_SESSION['mensaje_exito_global'])): ?>
        <div class="message success" style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_exito_global']); unset($_SESSION['mensaje_exito_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_error_global'])): ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_error_global']); unset($_SESSION['mensaje_error_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_info_global'])): ?>
        <div class="message info" style="background-color: #d1ecf1; color: #0c5460; padding: 10px; border: 1px solid #bee5eb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_info_global']); unset($_SESSION['mensaje_info_global']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_temporal_error'])): // Para errores no críticos como validación de fechas ?>
        <div class="message error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;"><?php echo htmlspecialchars($_SESSION['mensaje_temporal_error']); unset($_SESSION['mensaje_temporal_error']); ?></div>
    <?php endif; ?>


    <!-- Formulario de Filtros -->
    <div class="report-filters" style="margin-bottom: 30px; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
        <h4 style="margin-top:0; margin-bottom:15px; color: #495057;">Filtrar Reportes</h4>
        <form action="<?php echo BASE_URL; ?>empleado/reportes" method="GET" class="form-inline" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div class="form-group" style="flex: 1 1 200px;">
                <label for="fecha_desde" style="display: block; margin-bottom: 5px; font-weight: bold; color: #495057;">Desde:</label>
                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                       value="<?php echo isset($valores_filtros['fecha_desde']) ? htmlspecialchars($valores_filtros['fecha_desde']) : ''; ?>" 
                       style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
            </div>
            <div class="form-group" style="flex: 1 1 200px;">
                <label for="fecha_hasta" style="display: block; margin-bottom: 5px; font-weight: bold; color: #495057;">Hasta:</label>
                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                       value="<?php echo isset($valores_filtros['fecha_hasta']) ? htmlspecialchars($valores_filtros['fecha_hasta']) : ''; ?>" 
                       style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 8px 20px; font-size: 16px; color: #fff; background-color: #007bff; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.2s;">Aplicar Filtros</button>
            <a href="<?php echo BASE_URL; ?>empleado/reportes" class="btn btn-secondary" style="padding: 8px 15px; font-size: 16px; color: #fff; background-color: #6c757d; border: none; border-radius: 4px; text-decoration: none; transition: background-color 0.2s;">Limpiar Filtros</a>
        </form>
    </div>

    <!-- Sección: Citas por Estado -->
    <div class="report-section" style="margin-bottom: 30px;">
        <h3 style="color: #343a40; border-bottom: 2px solid #007bff; padding-bottom: 8px; margin-bottom: 15px;">Conteo de Citas por Estado</h3>
        <?php if (!empty($reporte_citas_por_estado) || (isset($reporte_citas_por_estado) && is_array($reporte_citas_por_estado))): ?>
            <table class="table table-striped table-bordered" style="width: 100%; border-collapse: collapse; font-size: 15px;">
                <thead style="background-color: #e9ecef; color: #495057;">
                    <tr>
                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Estado de la Cita</th>
                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: right;">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_citas_reporte_estado = 0;
                    foreach ($all_possible_estados_cita as $estado): 
                        $conteo = isset($reporte_citas_por_estado[$estado]) ? (int)$reporte_citas_por_estado[$estado] : 0;
                        $total_citas_reporte_estado += $conteo;
                    ?>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($estado); ?></td>
                            <td style="padding: 10px; border: 1px solid #dee2e6; text-align: right;"><?php echo $conteo; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if ($total_citas_reporte_estado > 0): ?>
                <tfoot style="font-weight: bold; background-color: #f8f9fa;">
                    <tr>
                        <td style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Total Citas en Periodo</td>
                        <td style="padding: 10px; border: 1px solid #dee2e6; text-align: right;"><?php echo $total_citas_reporte_estado; ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
            <?php if ($total_citas_reporte_estado == 0): ?>
                 <p style="color: #6c757d; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">No se encontraron citas para los filtros seleccionados en este reporte.</p>
            <?php endif; ?>
        <?php else: ?>
            <p style="color: #6c757d; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">No hay datos disponibles para el reporte de citas por estado o hubo un error al cargarlos.</p>
        <?php endif; ?>
    </div>

    <!-- Sección: Resumen de Servicios Realizados -->
    <div class="report-section" style="margin-bottom: 30px;">
        <h3 style="color: #343a40; border-bottom: 2px solid #28a745; padding-bottom: 8px; margin-bottom: 15px;">Resumen de Servicios Realizados</h3>
        <?php if (!empty($reporte_servicios_realizados)): ?>
            <table class="table table-striped table-bordered" style="width: 100%; border-collapse: collapse; font-size: 15px;">
                <thead style="background-color: #e9ecef; color: #495057;">
                    <tr>
                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Nombre del Servicio</th>
                        <th style="padding: 10px; border: 1px solid #dee2e6; text-align: right;">Cantidad Realizada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_servicios_reporte = 0;
                    foreach ($reporte_servicios_realizados as $servicio_info): 
                        $total_servicios_reporte += (int)$servicio_info['conteo'];
                    ?>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($servicio_info['servicio_nombre']); ?></td>
                            <td style="padding: 10px; border: 1px solid #dee2e6; text-align: right;"><?php echo htmlspecialchars($servicio_info['conteo']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                 <?php if ($total_servicios_reporte > 0): ?>
                <tfoot style="font-weight: bold; background-color: #f8f9fa;">
                    <tr>
                        <td style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Total Servicios en Periodo</td>
                        <td style="padding: 10px; border: 1px solid #dee2e6; text-align: right;"><?php echo $total_servicios_reporte; ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        <?php else: ?>
            <p style="color: #6c757d; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">No se encontraron servicios realizados para los filtros seleccionados o hubo un error al cargarlos.</p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <a href="<?php echo BASE_URL; ?>empleado/dashboard" class="btn btn-info" style="padding: 10px 20px; font-size: 16px; color: #fff; background-color: #17a2b8; border: none; border-radius: 4px; text-decoration: none;">Volver al Panel</a>
    </div>

</div>