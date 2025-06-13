<?php
// filepath: c:\xampp\htdocs\Proyecto-clip\app\Views\cliente\citas\reservar_cita.php

// Asegurarse de que las variables necesarias estén definidas, aunque sea como arrays vacíos
$servicios = $servicios ?? [];
$form_data = $form_data ?? [];
$form_errors = $form_errors ?? [];

// Para la URL base en JavaScript
$baseUrl = BASE_URL ?? '/'; 
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h1 class="h4 mb-0 text-center">Reservar Nueva Cita</h1>
                </div>
                <div class="card-body">
                    <?php
                    // Mensajes globales (ya gestionados por main_layout.php, pero pueden dejarse por si esta vista se usa sin el layout)
                    if (isset($_SESSION['mensaje_error_global_form'])) { // Usar una variable de sesión específica para el formulario si es necesario
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['mensaje_error_global_form']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        unset($_SESSION['mensaje_error_global_form']);
                    }
                    if (isset($_SESSION['mensaje_exito_global_form'])) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['mensaje_exito_global_form']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        unset($_SESSION['mensaje_exito_global_form']);
                    }
                    // No es necesario repetir los mensajes globales si main_layout.php ya los maneja.
                    // Si se dejan, asegurarse de que no interfieran o se muestren duplicados.
                    // Por simplicidad, los he comentado aquí asumiendo que main_layout.php los gestiona.
                    /*
                    if (isset($_SESSION['mensaje_error_global'])) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['mensaje_error_global']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        unset($_SESSION['mensaje_error_global']);
                    }
                    // ... y para otros tipos de mensajes ...
                    */
                    ?>

                    <form action="<?php echo rtrim($baseUrl, '/'); ?>/citas/guardar" method="POST" id="formReservarCita">
                        
                        <div class="form-group mb-3">
                            <label for="servicio_id" class="form-label">Servicio:</label>
                            <select name="servicio_id" id="servicio_id" class="form-select <?php echo isset($form_errors['servicio_id']) ? 'is-invalid' : ''; ?>" required>
                                <option value="">Selecciona un servicio</option>
                                <?php if (!empty($servicios)): ?>
                                    <?php foreach ($servicios as $servicio): ?>
                                        <option value="<?php echo htmlspecialchars($servicio['id']); ?>" 
                                                data-duracion="<?php echo htmlspecialchars($servicio['duracion_minutos'] ?? 'N/A'); ?>"
                                                <?php echo (isset($form_data['servicio_id']) && $form_data['servicio_id'] == $servicio['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($servicio['nombre']); ?> 
                                            (<?php echo htmlspecialchars($servicio['duracion_minutos'] ?? 'Duración no especificada'); ?> min)
                                            - $<?php echo htmlspecialchars(number_format($servicio['precio'] ?? 0, 0, ',', '.')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay servicios disponibles o hubo un error al cargarlos.</option>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($form_errors['servicio_id'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['servicio_id']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group mb-3">
                            <label for="fecha_hora_cita" class="form-label">Fecha y Hora de la Cita:</label>
                            <input type="datetime-local" name="fecha_hora_cita" id="fecha_hora_cita" 
                                   class="form-control <?php echo isset($form_errors['fecha_hora_cita']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo isset($form_data['fecha_hora_cita_input']) ? htmlspecialchars($form_data['fecha_hora_cita_input']) : (isset($form_data['fecha_hora_cita']) ? htmlspecialchars($form_data['fecha_hora_cita']) : ''); ?>" 
                                   required>
                            <?php if (isset($form_errors['fecha_hora_cita'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['fecha_hora_cita']); ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Selecciona primero un servicio y luego la fecha/hora.</small>
                        </div>

                        <div class="form-group mb-3" id="empleado_container" style="display: none;">
                            <label for="empleado_id" class="form-label">Empleado:</label>
                            <select name="empleado_id" id="empleado_id" class="form-select <?php echo isset($form_errors['empleado_id']) ? 'is-invalid' : ''; ?>" required disabled>
                                <option value="">Selecciona un servicio y fecha/hora primero</option>
                            </select>
                            <small id="mensaje_empleado" class="form-text text-muted"></small>
                            <?php if (isset($form_errors['empleado_id'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['empleado_id']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group mb-3">
                            <label for="notas_cliente" class="form-label">Notas Adicionales (opcional):</label>
                            <textarea name="notas_cliente" id="notas_cliente" class="form-control <?php echo isset($form_errors['notas_cliente']) ? 'is-invalid' : ''; ?>" rows="3"><?php echo isset($form_data['notas_cliente']) ? htmlspecialchars($form_data['notas_cliente']) : ''; ?></textarea>
                            <?php if (isset($form_errors['notas_cliente'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['notas_cliente']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Reservar Cita</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const servicioSelect = document.getElementById('servicio_id');
    const fechaHoraInput = document.getElementById('fecha_hora_cita');
    const empleadoSelect = document.getElementById('empleado_id');
    const empleadoContainer = document.getElementById('empleado_container');
    const mensajeEmpleado = document.getElementById('mensaje_empleado');
    const baseUrl = "<?php echo rtrim($baseUrl, '/'); ?>/";

    /**
     * Limpia el select de empleados y actualiza su estado.
     * @param {string} mensajeParaSelect - El mensaje a mostrar en la opción por defecto del select.
     * @param {boolean} ocultarContenedorSiNoHayDatosBase - Si es true, oculta el contenedor. Si es false, lo mantiene/hace visible.
     */
    function limpiarSelectEmpleados(mensajeParaSelect = 'Selecciona un servicio y fecha/hora para ver empleados', ocultarContenedorSiNoHayDatosBase = true) {
        if (!empleadoSelect) return;
        empleadoSelect.innerHTML = `<option value="">${mensajeParaSelect}</option>`;
        empleadoSelect.disabled = true;
        
        if (mensajeEmpleado) {
            
            mensajeEmpleado.textContent = ocultarContenedorSiNoHayDatosBase ? '' : mensajeParaSelect;
        }
        
        if (empleadoContainer) {
            if (ocultarContenedorSiNoHayDatosBase) {
                empleadoContainer.style.display = 'none';
            } else {
                
                empleadoContainer.style.display = 'block';
            }
        }
    }

    async function cargarEmpleadosDisponibles() {
        const servicioId = servicioSelect.value;
        const fechaHora = fechaHoraInput.value;

        if (!servicioId || !fechaHora) {
            // No hay servicio o fecha, ocultar contenedor y mostrar mensaje por defecto
            limpiarSelectEmpleados('Selecciona un servicio y fecha/hora para ver empleados', true);
            return;
        }

        // Hay servicio y fecha, mostrar contenedor y empezar a cargar
        if (empleadoContainer) empleadoContainer.style.display = 'block';
        if (empleadoSelect) {
            empleadoSelect.disabled = true;
            empleadoSelect.innerHTML = '<option value="">Cargando empleados...</option>';
        }
        if (mensajeEmpleado) mensajeEmpleado.textContent = 'Buscando empleados disponibles...';

        try {
            const response = await fetch(`${baseUrl}api/citas/empleados-disponibles?servicio_id=${servicioId}&fecha_hora=${encodeURIComponent(fechaHora)}`);
            
            if (!response.ok) {
                let errorMsg = 'Error al cargar empleados. Código: ' + response.status;
                try {
                    const errorData = await response.json();
                    if (errorData && (errorData.error || errorData.message)) {
                        errorMsg = errorData.error || errorData.message;
                    }
                } catch (e) { /* No hacer nada si el cuerpo del error no es JSON */ }
                throw new Error(errorMsg);
            }

            const data = await response.json();

            if (data.error) {
                // Error desde la API, mostrar mensaje en select y small text, mantener contenedor visible
                limpiarSelectEmpleados(data.error, false);
                return;
            }
            
            if (data.mensaje && (!data.empleados || data.empleados.length === 0)) {
                 // Mensaje informativo (ej. "no disponibles"), mostrar en select y small text, mantener contenedor visible
                 limpiarSelectEmpleados(data.mensaje, false);
                 return;
            }

            if (data.empleados && data.empleados.length > 0) {
                if (empleadoSelect) {
                    empleadoSelect.innerHTML = '<option value="">Selecciona un empleado</option>';
                    data.empleados.forEach(empleado => {
                        const option = document.createElement('option');
                        option.value = empleado.id;
                        option.textContent = empleado.nombre_completo;
                        <?php if (isset($form_data['empleado_id'])): ?>
                        // Si hay un valor previo (ej. por error de validación), seleccionarlo
                        if (empleado.id == <?php echo json_encode($form_data['empleado_id']); ?>) {
                            option.selected = true;
                        }
                        <?php endif; ?>
                        empleadoSelect.appendChild(option);
                    });
                    empleadoSelect.disabled = false;
                }
                if (mensajeEmpleado) mensajeEmpleado.textContent = 'Empleados cargados. Por favor, selecciona uno.';
            } else {
                // No hay empleados, pero no hay error ni mensaje específico de la API
                const mensajeDefault = 'No hay empleados disponibles para este servicio en la fecha y hora seleccionadas.';
                limpiarSelectEmpleados(data.mensaje || mensajeDefault, false); // Mantener contenedor visible
            }

        } catch (error) {
            console.error('Error en fetch:', error);
            const errorParaUsuario = error.message.includes('Failed to fetch') ? 'Error de red o API no disponible.' : error.message;
            // Error en la petición, mostrar en select y small text, mantener contenedor visible
            limpiarSelectEmpleados('Error al cargar empleados. ' + errorParaUsuario, false);
        }
    }

    if (servicioSelect && fechaHoraInput && empleadoSelect && empleadoContainer && mensajeEmpleado) {
        // Estado inicial: si ya hay valores (ej. al recargar página con error de validación), intentar cargar.
        // Si no, limpiar y ocultar.
        if (servicioSelect.value && fechaHoraInput.value) {
             cargarEmpleadosDisponibles();
        } else {
            limpiarSelectEmpleados('Selecciona un servicio y fecha/hora para ver empleados', true);
        }

        servicioSelect.addEventListener('change', cargarEmpleadosDisponibles);
        fechaHoraInput.addEventListener('change', cargarEmpleadosDisponibles);
        
        // Debounce para el input de fecha/hora
        let debounceTimer;
        fechaHoraInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(cargarEmpleadosDisponibles, 600); // Ajusta el delay si es necesario
        });

    } else {
        console.error('Algunos elementos del formulario (servicio_id, fecha_hora_cita, empleado_id, empleado_container, mensaje_empleado) no fueron encontrados en el DOM.');
        if(empleadoContainer) empleadoContainer.style.display = 'block'; // Intentar mostrar para el mensaje de error
        if(mensajeEmpleado) mensajeEmpleado.textContent = 'Error: Faltan elementos del formulario en la página.';
    }

    // Establecer la fecha y hora mínima para el input datetime-local
    if (fechaHoraInput) {
        const now = new Date();
        // Añadir un pequeño buffer (ej. 5 minutos) para evitar seleccionar el minuto exacto actual si el procesamiento tarda
        now.setMinutes(now.getMinutes() + 5); 

        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        
        fechaHoraInput.min = `${year}-${month}-${day}T${hours}:${minutes}`;
    }
});
</script>

