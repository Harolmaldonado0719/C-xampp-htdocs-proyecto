<h1><?php echo htmlspecialchars($pageTitle ?? "Reportes Generales"); ?></h1>

<div class="reports-container">

    <section class="report-section">
        <h2><i class="fas fa-users"></i> Reporte de Usuarios</h2>
        <div class="stat-card">
            <h4>Total de Usuarios Registrados</h4>
            <p><?php echo htmlspecialchars($reportData['totalUsuarios'] ?? 0); ?></p>
        </div>
        <!-- Aquí podrías añadir más estadísticas de usuarios si las implementas -->
        <!-- 
        <div class="stat-card">
            <h4>Total de Clientes</h4>
            <p><?php echo htmlspecialchars($reportData['totalClientes'] ?? 0); ?></p>
        </div>
        <div class="stat-card">
            <h4>Total de Empleados</h4>
            <p><?php echo htmlspecialchars($reportData['totalEmpleados'] ?? 0); ?></p>
        </div>
        -->
    </section>

    <section class="report-section">
        <h2><i class="fas fa-calendar-alt"></i> Reporte de Citas</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Citas Pendientes</h4>
                <p><?php echo htmlspecialchars($reportData['citasPendientes'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h4>Citas Confirmadas</h4>
                <p><?php echo htmlspecialchars($reportData['citasConfirmadas'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h4>Citas Completadas</h4>
                <p><?php echo htmlspecialchars($reportData['citasCompletadas'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h4>Citas Canceladas</h4>
                <p><?php echo htmlspecialchars($reportData['citasCanceladas'] ?? 0); ?></p>
            </div>
        </div>
    </section>

    <section class="report-section">
        <h2><i class="fas fa-headset"></i> Reporte de Solicitudes (PQR)</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h4>PQR Abiertas</h4>
                <p><?php echo htmlspecialchars($reportData['pqrAbiertas'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h4>PQR En Proceso</h4>
                <p><?php echo htmlspecialchars($reportData['pqrEnProceso'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h4>PQR Resueltas</h4>
                <p><?php echo htmlspecialchars($reportData['pqrResueltas'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h4>PQR Cerradas</h4>
                <p><?php echo htmlspecialchars($reportData['pqrCerradas'] ?? 0); ?></p>
            </div>
        </div>
    </section>

</div>

<style>
    .reports-container {
        padding: 20px;
    }
    .report-section {
        background-color: #f8f9fa;
        padding: 20px;
        margin-bottom: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .report-section h2 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }
    .report-section h2 .fas { /* Font Awesome icons */
        margin-right: 10px;
        color: #007bff;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .stat-card {
        background-color: #fff;
        padding: 15px;
        border-radius: 5px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
    }
    .stat-card h4 {
        margin-top: 0;
        margin-bottom: 8px;
        font-size: 1.1em;
        color: #555;
    }
    .stat-card p {
        font-size: 2em;
        margin: 0;
        font-weight: bold;
        color: #007bff;
    }
</style>
<!-- Asegúrate de tener Font Awesome si quieres usar los iconos, o elimínalos -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->