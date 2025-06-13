<!-- filepath: app/Views/notificaciones/cliente/lista_notificaciones.php -->
<h1><?php echo htmlspecialchars($pageTitle ?? "Mis Notificaciones"); ?></h1>

<?php if (isset($_SESSION['mensaje_error_notificaciones'])): ?>
    <div class="message error"><?php echo htmlspecialchars($_SESSION['mensaje_error_notificaciones']); unset($_SESSION['mensaje_error_notificaciones']); ?></div>
<?php endif; ?>

<?php if (empty($notificaciones)): ?>
    <p>No tienes notificaciones nuevas.</p>
<?php else: ?>
    <ul class="notificaciones-lista">
        <?php foreach ($notificaciones as $notificacion): ?>
            <li class="notificacion-item <?php echo $notificacion['fecha_lectura'] ? 'leida' : 'no-leida'; ?> tipo-<?php echo htmlspecialchars($notificacion['tipo'] ?? 'info'); ?>">
                <div class="notificacion-header">
                    <span class="notificacion-fecha"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($notificacion['fecha_creacion']))); ?></span>
                    <?php if (!$notificacion['fecha_lectura']): ?>
                        <span class="badge-no-leida">Nueva</span>
                    <?php endif; ?>
                </div>
                <div class="notificacion-mensaje">
                    <?php echo nl2br(htmlspecialchars($notificacion['mensaje'])); ?>
                </div>
                <?php if ($notificacion['enlace']): ?>
                    <div class="notificacion-enlace">
                        <a href="<?php echo htmlspecialchars(filter_var($notificacion['enlace'], FILTER_SANITIZE_URL)); ?>" class="btn btn-sm btn-link">Ver detalle</a>
                    </div>
                <?php endif; ?>
                <?php if (!$notificacion['fecha_lectura']): ?>
                    <div class="notificacion-accion">
                        <!-- Podríamos usar un form POST o un enlace GET con confirmación para marcar como leída -->
                        <a href="<?php echo BASE_URL . 'notificaciones/marcar-leida/' . htmlspecialchars($notificacion['id']); ?>" class="btn btn-sm btn-outline-secondary marcar-leida-btn">Marcar como leída</a>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<style>
    .notificaciones-lista { list-style: none; padding: 0; }
    .notificacion-item { 
        border: 1px solid #ddd; 
        margin-bottom: 10px; 
        padding: 15px; 
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .notificacion-item.no-leida { font-weight: bold; background-color: #fff; border-left: 5px solid #007bff; }
    .notificacion-item.leida { color: #555; background-color: #e9ecef; }
    .notificacion-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.9em; color: #777; }
    .notificacion-item.no-leida .notificacion-header { color: #333; }
    .badge-no-leida { background-color: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em; }
    .notificacion-mensaje { margin-bottom: 10px; }
    .notificacion-enlace a, .notificacion-accion a { font-size: 0.9em; }
    .tipo-error { border-left-color: #dc3545 !important; }
    .tipo-warning { border-left-color: #ffc107 !important; }
    .tipo-success { border-left-color: #28a745 !important; }
</style>
<!-- Podríamos añadir JS para marcar como leída vía AJAX en el futuro -->
<!--
<script>
document.addEventListener('DOMContentLoaded', function() {
    const marcarLeidaBotones = document.querySelectorAll('.marcar-leida-btn');
    marcarLeidaBotones.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            // Aquí iría la lógica AJAX para llamar a la URL y actualizar la UI
            // fetch(url, { method: 'POST' or 'GET' depending on your route for marking as read })
            // .then(response => response.json())
            // .then(data => {
            //     if(data.success) {
            //         this.closest('.notificacion-item').classList.remove('no-leida');
            //         this.closest('.notificacion-item').classList.add('leida');
            //         this.remove(); // Quitar el botón
            //     } else {
            //         alert(data.message || 'Error al marcar como leída');
            //     }
            // })
            // .catch(err => console.error('Error AJAX:', err));
            // Por ahora, simplemente redirige:
            window.location.href = url;
        });
    });
});
</script>
-->