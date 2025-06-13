<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/ProductoModel.php';
require_once __DIR__ . '/../Models/CitaModel.php';
require_once __DIR__ . '/../Models/CategoriaModel.php';
require_once __DIR__ . '/../Models/NotificacionModel.php';
require_once __DIR__ . '/../Models/HorarioModel.php';
require_once __DIR__ . '/../Models/ServicioModel.php';
require_once __DIR__ . '/../Models/FacturaModel.php'; 
require_once __DIR__ . '/../Core/Validator.php';

class EmpleadoController {
    private $db;
    private $pdoConn;
    private $productoModel;
    private $citaModel;
    private $categoriaModel;
    private $notificacionModel;
    private $horarioModel;
    private $servicioModel;
    private $facturaModel; 

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $this->db = new Database();
            $this->pdoConn = $this->db->getPdoConnection();

            if (!$this->pdoConn) {
                throw new Exception("EmpleadoController: No se pudo obtener la conexión PDO.");
            }

            $this->productoModel = new ProductoModel($this->pdoConn);
            $this->citaModel = new CitaModel($this->pdoConn);
            $this->categoriaModel = new CategoriaModel($this->pdoConn);
            $this->notificacionModel = new NotificacionModel($this->pdoConn);
            $this->servicioModel = new ServicioModel($this->pdoConn);
            $this->facturaModel = new FacturaModel($this->pdoConn); 

            if (class_exists('App\Models\HorarioModel')) {
                $this->horarioModel = new \App\Models\HorarioModel($this->pdoConn);
            } elseif (class_exists('HorarioModel')) {
                $this->horarioModel = new HorarioModel($this->pdoConn);
            } else {
                 error_log("EmpleadoController Constructor: Clase HorarioModel no encontrada.");
                 $this->horarioModel = null;
            }


        } catch (Exception $e) {
            error_log("EmpleadoController Constructor: " . $e->getMessage());
            $errorMessage = "Error crítico del sistema al inicializar el panel de empleado. Contacte al administrador.";
            if (strpos($e->getMessage(), 'Class') !== false && strpos($e->getMessage(), 'not found') !== false) {
                 $errorMessage = "Error crítico del sistema (Clase HorarioModel o FacturaModel no encontrada). Contacte al administrador.";
            }
            $_SESSION['mensaje_error_global'] = $errorMessage;


            if (defined('BASE_URL') && !headers_sent() && !(defined('DEBUG_MODE') && DEBUG_MODE)) {
                 header("Location: " . BASE_URL . "login");
                 exit;
            }
            if(defined('DEBUG_MODE') && DEBUG_MODE){
                die("Error crítico del sistema al inicializar EmpleadoController: " . $e->getMessage() . ". Revise los logs.");
            }
        }
    }

    private function verificarAccesoEmpleado() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != (defined('ID_ROL_EMPLEADO') ? ID_ROL_EMPLEADO : 2) ) {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['mensaje_error_global'] = "Debes iniciar sesión para acceder a esta área.";
            } else {
                $_SESSION['mensaje_error_global'] = "No tienes permisos para acceder a esta sección.";
            }
            return false;
        }
        return true;
    }

    private function renderView($viewName, $data = []) {
        $viewPath = dirname(__DIR__) . '/Views/' . $viewName . '.php';

        if ($this->notificacionModel && isset($_SESSION['user_id']) && !isset($data['contador_notificaciones_no_leidas'])) {
            if (method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
                 $data['contador_notificaciones_no_leidas'] = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
            } else {
                $data['contador_notificaciones_no_leidas'] = 0;
                error_log("EmpleadoController::renderView - Método contarNoLeidasPorUsuarioId no existe en NotificacionModel.");
            }
        } elseif (!isset($data['contador_notificaciones_no_leidas'])) {
            $data['contador_notificaciones_no_leidas'] = 0;
        }

        extract($data);
        $layoutPath = dirname(__DIR__) . '/Views/layouts/main_layout.php';

        if (file_exists($layoutPath)) {
            $pageTitle = $data['pageTitle'] ?? 'Título por defecto';
            include $layoutPath;
        } else {
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                error_log("Error crítico: No se encontró ni el layout ni la vista específica: " . $viewPath);
                echo "Error: No se pudo cargar la página solicitada.";
            }
        }
    }

    public function dashboard() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Panel de Empleado";
        $active_page = 'dashboard_empleado';
        $notificaciones_no_leidas_resumen = [];
        $contador_notificaciones_no_leidas = 0;

        if ($this->notificacionModel && isset($_SESSION['user_id'])) {
            if (method_exists($this->notificacionModel, 'obtenerNoLeidasPorUsuario')) {
                $notificaciones_no_leidas_resumen = $this->notificacionModel->obtenerNoLeidasPorUsuario($_SESSION['user_id'], 5);
            }
            if (method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
                $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
            }
        }
        $this->renderView('empleado/dashboard_empleado', compact('pageTitle', 'active_page', 'notificaciones_no_leidas_resumen', 'contador_notificaciones_no_leidas'));
    }

    // ... (métodos de productos, agenda, etc. que ya tenías) ...
    public function listarProductos() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Gestionar Productos";
        $active_page = 'gestionar_productos';
        $productos = [];
        $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
        $contador_notificaciones_no_leidas = 0;

        if ($this->productoModel && method_exists($this->productoModel, 'obtenerTodos')) {
            $productos = $this->productoModel->obtenerTodos();
        } else {
            $_SESSION['mensaje_error_global'] = "No se pudo cargar el modelo de productos o el método obtenerTodos no existe.";
            error_log("EmpleadoController::listarProductos - ProductoModel no cargado o método obtenerTodos no existe.");
        }

        foreach ($productos as &$producto) {
            if (!empty($producto['imagen_url'])) {
                $producto['imagen_completa_url'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($producto['imagen_url']);
            } else {
                $producto['imagen_completa_url'] = BASE_URL . 'img/default-product.png';
            }
        }
        unset($producto);

        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }

        $this->renderView('empleado/productos/catalogo_disponible', compact('pageTitle', 'productos', 'active_page', 'contador_notificaciones_no_leidas'));
    }

    public function crearProductoForm() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Añadir Nuevo Producto";
        $active_page = 'gestionar_productos';
        $categorias = [];
        $contador_notificaciones_no_leidas = 0;

        if ($this->categoriaModel) {
            if (method_exists($this->categoriaModel, 'obtenerTodasCategoriasActivas')) {
                 $categorias = $this->categoriaModel->obtenerTodasCategoriasActivas();
            } elseif (method_exists($this->categoriaModel, 'obtenerTodasCategorias')) {
                $categorias = $this->categoriaModel->obtenerTodasCategorias();
            } elseif (method_exists($this->categoriaModel, 'obtenerTodas')) {
                $categorias = $this->categoriaModel->obtenerTodas();
            }
        }

        $form_data_key = 'crear_producto';
        $form_data = $_SESSION['form_data'][$form_data_key] ?? [];
        $form_errors = $_SESSION['form_errors'][$form_data_key] ?? [];
        $action_url = BASE_URL . 'empleado/productos/guardar';
        $is_editing = false;
        $producto_actual = null;

        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }

        $this->renderView('empleado/productos/crear', compact('pageTitle', 'categorias', 'form_data', 'form_errors', 'action_url', 'is_editing', 'active_page', 'producto_actual', 'contador_notificaciones_no_leidas'));
    }

    public function guardarProducto() {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header("Location: " . BASE_URL . "empleado/productos");
            exit;
        }
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio' => str_replace(',', '.', trim($_POST['precio'] ?? '')),
            'stock' => trim($_POST['stock'] ?? ''),
            'categoria_id' => trim($_POST['categoria_id'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        $validator = new Validator();
        $validator->validate($datos['nombre'], 'Nombre', ['required', 'max:100']);
        $validator->validate($datos['precio'], 'Precio', ['required', 'numeric', 'minVal:0', 'maxVal:99999999.99']);
        $validator->validate($datos['stock'], 'Stock', ['required', 'integer', 'minVal:0', 'maxVal:99999']);
        $validator->validate($datos['categoria_id'], 'Categoría', ['required', 'integer']);
        $validator->validate($datos['descripcion'], 'Descripción', ['max:1000']);

        $nombreArchivoImagen = null;
        $uploadDir = rtrim(APP_UPLOAD_DIR, '/') . '/';

        if (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] == UPLOAD_ERR_OK) {
            if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true)) {
                 $validator->addError('imagen_url', "Error al crear el directorio de subidas: " . $uploadDir);
            } else {
                $targetFile = $uploadDir . basename($_FILES['imagen_url']['name']);
                $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                $newFileName = uniqid('prod_', true) . '.' . $imageFileType;
                $targetFile = $uploadDir . $newFileName;
                if (move_uploaded_file($_FILES['imagen_url']['tmp_name'], $targetFile)) {
                    $nombreArchivoImagen = $newFileName;
                } else {
                    $validator->addError('imagen_url', 'Error al subir la imagen.');
                }
            }
        } elseif (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] != UPLOAD_ERR_NO_FILE) {
             switch ($_FILES['imagen_url']['error']) {
                case UPLOAD_ERR_INI_SIZE: case UPLOAD_ERR_FORM_SIZE: $validator->addError('imagen_url', 'El archivo es demasiado grande.'); break;
                default: $validator->addError('imagen_url', 'Error desconocido al subir el archivo.'); break;
            }
        }
        $datos['imagen_url'] = $nombreArchivoImagen;

        if (!$validator->hasErrors()) {
            if ($this->productoModel && method_exists($this->productoModel, 'crear')) {
                if ($this->productoModel->crear($datos)) {
                    $_SESSION['mensaje_exito_global'] = "Producto '" . htmlspecialchars($datos['nombre']) . "' creado exitosamente.";
                    unset($_SESSION['form_data']['crear_producto'], $_SESSION['form_errors']['crear_producto']);
                    header("Location: " . BASE_URL . "empleado/productos");
                    exit;
                } else {
                    $_SESSION['mensaje_error_global'] = "Error al crear el producto en la base de datos.";
                    if ($nombreArchivoImagen && file_exists($uploadDir . $nombreArchivoImagen)) { @unlink($uploadDir . $nombreArchivoImagen); }
                }
            } else {
                $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de producto no disponible.";
                error_log("EmpleadoController::guardarProducto - productoModel no disponible o método crear no existe.");
                if ($nombreArchivoImagen && file_exists($uploadDir . $nombreArchivoImagen)) { @unlink($uploadDir . $nombreArchivoImagen); }
            }
        }
        $_SESSION['form_data']['crear_producto'] = $_POST;
        $_SESSION['form_errors']['crear_producto'] = $validator->getErrors();
        $_SESSION['mensaje_error_global'] = "Por favor, corrige los errores del formulario.";
        if ($nombreArchivoImagen && file_exists($uploadDir . $nombreArchivoImagen) && $validator->hasErrors()) { @unlink($uploadDir . $nombreArchivoImagen); }
        header("Location: " . BASE_URL . "empleado/productos/crear");
        exit;
    }

    public function editarProductoForm($id_str) {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $id = filter_var($id_str, FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['mensaje_error_global'] = "ID de producto inválido.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $producto_actual = $this->productoModel ? $this->productoModel->obtenerPorId($id) : null;
        $active_page = 'gestionar_productos';
        $contador_notificaciones_no_leidas = 0;

        if (!$producto_actual) {
            $_SESSION['mensaje_error_global'] = "Producto no encontrado.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $categorias = [];
        if ($this->categoriaModel) {
            if (method_exists($this->categoriaModel, 'obtenerTodasCategoriasActivas')) {
                 $categorias = $this->categoriaModel->obtenerTodasCategoriasActivas();
            } elseif (method_exists($this->categoriaModel, 'obtenerTodasCategorias')) {
                $categorias = $this->categoriaModel->obtenerTodasCategorias();
            } elseif (method_exists($this->categoriaModel, 'obtenerTodas')) {
                $categorias = $this->categoriaModel->obtenerTodas();
            }
        }
        $pageTitle = 'Editar Producto: ' . htmlspecialchars($producto_actual['nombre']);
        $form_data_key = 'editar_producto_' . $id;
        $form_data = $_SESSION['form_data'][$form_data_key] ?? $producto_actual;
        $form_errors = $_SESSION['form_errors'][$form_data_key] ?? [];
        if (!isset($form_data['imagen_url_actual'])) {
            $form_data['imagen_url_actual'] = $producto_actual['imagen_url'];
        }
        if (empty($_SESSION['form_data'][$form_data_key]) && isset($form_data['precio'])) {
            $form_data['precio'] = number_format((float)$form_data['precio'], 2, '.', '');
        }
        $action_url = BASE_URL . 'empleado/productos/actualizar/' . $id;
        $is_editing = true;

        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }
        $this->renderView('empleado/productos/crear', compact('pageTitle', 'categorias', 'form_data', 'form_errors', 'action_url', 'is_editing', 'id', 'active_page', 'producto_actual', 'contador_notificaciones_no_leidas'));
    }

    public function actualizarProducto($id_str) {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $id = filter_var($id_str, FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['mensaje_error_global'] = "ID de producto inválido.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $productoExistente = $this->productoModel ? $this->productoModel->obtenerPorId($id) : null;
        if (!$productoExistente) {
            $_SESSION['mensaje_error_global'] = "Producto no encontrado para actualizar.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio' => str_replace(',', '.', trim($_POST['precio'] ?? '')),
            'stock' => trim($_POST['stock'] ?? ''),
            'categoria_id' => trim($_POST['categoria_id'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
        $validator = new Validator();
        $validator->validate($datos['nombre'], 'Nombre', ['required', 'max:100']);
        $validator->validate($datos['precio'], 'Precio', ['required', 'numeric', 'minVal:0', 'maxVal:99999999.99']);
        $validator->validate($datos['stock'], 'Stock', ['required', 'integer', 'minVal:0', 'maxVal:99999']);
        $validator->validate($datos['categoria_id'], 'Categoría', ['required', 'integer']);
        $validator->validate($datos['descripcion'], 'Descripción', ['max:1000']);

        $nombreArchivoImagen = $productoExistente['imagen_url'];
        $uploadDir = rtrim(APP_UPLOAD_DIR, '/') . '/';
        $imagenSubidaNueva = false;
        $imagenEliminadaCheckbox = isset($_POST['eliminar_imagen_actual']) && $_POST['eliminar_imagen_actual'] == '1';

        if ($imagenEliminadaCheckbox) {
            if ($productoExistente['imagen_url'] && file_exists($uploadDir . $productoExistente['imagen_url'])) {
                @unlink($uploadDir . $productoExistente['imagen_url']);
            }
            $nombreArchivoImagen = null;
            $datos['imagen_url'] = null;
        }
        if (!$imagenEliminadaCheckbox && isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] == UPLOAD_ERR_OK) {
            if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true)) {
                 $validator->addError('imagen_url', "Error al crear el directorio de subidas: " . $uploadDir);
            } else {
                $targetFile = $uploadDir . basename($_FILES['imagen_url']['name']);
                $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                $newFileName = uniqid('prod_edit_', true) . '.' . $imageFileType;
                $targetFile = $uploadDir . $newFileName;
                if (move_uploaded_file($_FILES['imagen_url']['tmp_name'], $targetFile)) {
                    if ($productoExistente['imagen_url'] && $productoExistente['imagen_url'] !== $newFileName && file_exists($uploadDir . $productoExistente['imagen_url'])) {
                        @unlink($uploadDir . $productoExistente['imagen_url']);
                    }
                    $nombreArchivoImagen = $newFileName;
                    $imagenSubidaNueva = true;
                } else {
                    $validator->addError('imagen_url', 'Error al subir la nueva imagen.');
                }
            }
        } elseif (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] != UPLOAD_ERR_NO_FILE) {
             switch ($_FILES['imagen_url']['error']) {
                 case UPLOAD_ERR_INI_SIZE: case UPLOAD_ERR_FORM_SIZE: $validator->addError('imagen_url', 'El archivo es demasiado grande.'); break;
                 default: $validator->addError('imagen_url', 'Error desconocido al subir el archivo.'); break;
            }
        } else {
            if (!$imagenEliminadaCheckbox) { $datos['imagen_url'] = $productoExistente['imagen_url']; }
        }
        $form_data_key = 'editar_producto_' . $id;
        if (!$validator->hasErrors()) {
            if ($this->productoModel && method_exists($this->productoModel, 'actualizar')) {
                $datos['imagen_url'] = $nombreArchivoImagen;
                if ($this->productoModel->actualizar($id, $datos)) {
                    $_SESSION['mensaje_exito_global'] = "Producto '" . htmlspecialchars($datos['nombre']) . "' actualizado exitosamente.";
                    unset($_SESSION['form_data'][$form_data_key], $_SESSION['form_errors'][$form_data_key]);
                    header('Location: ' . BASE_URL . 'empleado/productos');
                    exit;
                } else {
                    $_SESSION['mensaje_error_global'] = "Error al actualizar el producto en la base de datos.";
                }
            } else {
                $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de producto no disponible para actualizar.";
                error_log("EmpleadoController::actualizarProducto - productoModel no disponible o método actualizar no existe.");
            }
        }
        $_SESSION['form_data'][$form_data_key] = array_merge($_POST, ['imagen_url_actual' => $productoExistente['imagen_url']]);
        $_SESSION['form_errors'][$form_data_key] = $validator->getErrors();
        $_SESSION['mensaje_error_global'] = 'Por favor, corrige los errores en el formulario.';
        if ($imagenSubidaNueva && $nombreArchivoImagen !== $productoExistente['imagen_url'] && file_exists($uploadDir . $nombreArchivoImagen) && $validator->hasErrors()) {
            @unlink($uploadDir . $nombreArchivoImagen);
        }
        header('Location: ' . BASE_URL . 'empleado/productos/editar/' . $id);
        exit;
    }

    public function eliminarProducto($id_str) {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $id = filter_var($id_str, FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['mensaje_error_global'] = "ID de producto inválido.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        $producto = $this->productoModel ? $this->productoModel->obtenerPorId($id) : null;
        if (!$producto) {
            $_SESSION['mensaje_error_global'] = "Producto no encontrado para eliminar/desactivar.";
            header('Location: ' . BASE_URL . 'empleado/productos');
            exit;
        }
        if ($this->productoModel && method_exists($this->productoModel, 'eliminar')) {
            if ($this->productoModel->eliminar($id)) {
                $_SESSION['mensaje_exito_global'] = "Producto '" . htmlspecialchars($producto['nombre']) . "' marcado como inactivo.";
            } else {
                $_SESSION['mensaje_error_global'] = "No se pudo marcar el producto como inactivo.";
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de producto no disponible para desactivar.";
            error_log("EmpleadoController::eliminarProducto - productoModel no disponible o método eliminar no existe.");
        }
        header('Location: ' . BASE_URL . 'empleado/productos');
        exit;
    }

    public function agenda() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $empleado_id = $_SESSION['user_id'];
        $pageTitle = "Mi Agenda de Citas";
        $active_page = 'mi_agenda';
        $contador_notificaciones_no_leidas = 0;
        $fecha_filtro_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fecha_filtro_fin = $_GET['fecha_fin'] ?? date('Y-m-d', strtotime($fecha_filtro_inicio . ' +6 days'));
        $_SESSION['last_cita_filters'] = ['fecha_inicio' => $fecha_filtro_inicio, 'fecha_fin' => $fecha_filtro_fin];
        $citas = [];
        if ($this->citaModel) {
            $citas = $this->citaModel->obtenerCitasPorEmpleado($empleado_id, $fecha_filtro_inicio, $fecha_filtro_fin);
        }
        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }
        $this->renderView('empleado/agenda/agenda', compact('pageTitle', 'citas', 'fecha_filtro_inicio', 'fecha_filtro_fin', 'active_page', 'contador_notificaciones_no_leidas'));
    }

    public function actualizarEstadoCita($id_cita_str) {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Método no permitido.";
            $this->redirigirAAgendaConFiltros();
            exit;
        }

        $id_cita = filter_var($id_cita_str, FILTER_VALIDATE_INT);
        $nuevo_estado = $_POST['estado_cita'] ?? null;
        $notas_empleado = isset($_POST['notas_empleado']) ? trim($_POST['notas_empleado']) : null;

        if (!$id_cita || empty($nuevo_estado)) {
            $_SESSION['mensaje_error_global'] = "Datos incompletos o inválidos para actualizar la cita.";
            $this->redirigirAAgendaConFiltros();
            exit;
        }

        $cita_actual = $this->citaModel ? $this->citaModel->obtenerCitaPorId($id_cita) : null;

        if (!$cita_actual || $cita_actual['empleado_id'] != $_SESSION['user_id']) {
            $_SESSION['mensaje_error_global'] = "No tienes permiso para modificar esta cita o la cita no existe.";
            $this->redirigirAAgendaConFiltros();
            exit;
        }

        if ($this->citaModel && method_exists($this->citaModel, 'actualizarEstadoCita')) {
            if ($this->citaModel->actualizarEstadoCita($id_cita, $nuevo_estado, $notas_empleado)) {
                $_SESSION['mensaje_exito_global'] = "Estado de la cita #{$id_cita} actualizado correctamente a " . htmlspecialchars($nuevo_estado) . ".";

                if (($nuevo_estado === 'CONFIRMADA' || $nuevo_estado === 'Confirmada') && $this->facturaModel) {
                    if (isset($cita_actual['servicio_precio']) && is_numeric($cita_actual['servicio_precio']) &&
                        isset($cita_actual['cliente_id']) && isset($cita_actual['empleado_id']) &&
                        isset($cita_actual['servicio_nombre'])) {
                        
                        $numero_factura_generado = $this->facturaModel->generarNumeroFactura();

                        $datosFactura = [
                            'cita_id' => $id_cita,
                            'numero_factura' => $numero_factura_generado,
                            'cliente_id' => $cita_actual['cliente_id'],
                            'empleado_id' => $cita_actual['empleado_id'],
                            'servicio_nombre_en_factura' => $cita_actual['servicio_nombre'],
                            'monto_total' => $cita_actual['servicio_precio'],
                            'estado_factura' => 'Pendiente' // Estado inicial de la factura
                        ];

                        $factura_id = $this->facturaModel->crear($datosFactura);
                        if ($factura_id) {
                            $_SESSION['mensaje_exito_global'] = ($_SESSION['mensaje_exito_global'] ?? "") . " Factura #{$numero_factura_generado} generada.";
                        } else {
                            $error_factura_msg = " Error al generar la factura para la cita #{$id_cita}. Verifique que el número de factura no esté duplicado o contacte al administrador.";
                            $_SESSION['mensaje_error_global'] = isset($_SESSION['mensaje_error_global']) ? $_SESSION['mensaje_error_global'] . $error_factura_msg : $error_factura_msg;
                            error_log("EmpleadoController::actualizarEstadoCita - Error al crear factura para cita ID: {$id_cita}. Datos: " . json_encode($datosFactura));
                        }
                    } else {
                        $error_datos_factura_msg = " No se pudo generar la factura: faltan datos del servicio (precio) o de la cita.";
                         $_SESSION['mensaje_error_global'] = isset($_SESSION['mensaje_error_global']) ? $_SESSION['mensaje_error_global'] . $error_datos_factura_msg : $error_datos_factura_msg;
                        error_log("EmpleadoController::actualizarEstadoCita - Faltan datos para generar factura. Cita: " . json_encode($cita_actual));
                    }
                }
                // Lógica de notificación al cliente
                if ($this->notificacionModel && $this->servicioModel && isset($cita_actual['cliente_id']) && isset($cita_actual['servicio_id'])) {
                    $id_cliente_destino = $cita_actual['cliente_id'];
                    $nombre_servicio = $cita_actual['servicio_nombre'] ?? 'el servicio solicitado'; 
                    $fecha_cita_formateada = date('d/m/Y \a \l\a\s H:i', strtotime($cita_actual['fecha_hora_cita'])); 
                    $mensaje_cliente = "";
                    $tipo_notif = "";
                    $enlaceNotificacion = BASE_URL . 'mis-citas';

                    if ($nuevo_estado === 'CONFIRMADA' || $nuevo_estado === 'Confirmada') {
                        $mensaje_cliente = "¡Buenas noticias! Tu cita para {$nombre_servicio} el {$fecha_cita_formateada} ha sido CONFIRMADA.";
                        $tipo_notif = "CITA_CONFIRMADA";
                    } elseif ($nuevo_estado === 'CANCELADA' || $nuevo_estado === 'Cancelada') {
                        $mensaje_cliente = "Información importante: Tu cita para {$nombre_servicio} el {$fecha_cita_formateada} ha sido CANCELADA por el proveedor.";
                        $tipo_notif = "CITA_CANCELADA";
                    }

                    if (!empty($mensaje_cliente) && !empty($tipo_notif) && method_exists($this->notificacionModel, 'crear')) {
                        $this->notificacionModel->crear(
                            $id_cliente_destino,
                            $mensaje_cliente,
                            $tipo_notif,
                            $enlaceNotificacion
                        );
                    } elseif (empty($mensaje_cliente) || empty($tipo_notif)) {
                        // No hacer nada si no hay mensaje o tipo (ej. para 'Realizada')
                    } else {
                        error_log("EmpleadoController::actualizarEstadoCita - Método crear no existe en NotificacionModel o faltan datos para notificación.");
                    }
                } else {
                    error_log("EmpleadoController::actualizarEstadoCita - No se pudo crear notificación para el cliente. Modelos no disponibles o faltan datos de la cita (cliente_id, servicio_id).");
                }

            } else {
                $_SESSION['mensaje_error_global'] = "Error al actualizar el estado de la cita #{$id_cita}.";
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de citas no disponible para actualizar estado.";
            error_log("EmpleadoController::actualizarEstadoCita - CitaModel no disponible o método actualizarEstadoCita no existe.");
        }

        $this->redirigirAAgendaConFiltros();
        exit;
    }

    private function redirigirAAgendaConFiltros() {
        $redirect_url = BASE_URL . "empleado/agenda";
        $query_params = [];
        if (isset($_SESSION['last_cita_filters']['fecha_inicio'])) {
            $query_params['fecha_inicio'] = $_SESSION['last_cita_filters']['fecha_inicio'];
        }
        if (isset($_SESSION['last_cita_filters']['fecha_fin'])) {
            $query_params['fecha_fin'] = $_SESSION['last_cita_filters']['fecha_fin'];
        }
        if (!empty($query_params)) {
            $redirect_url .= '?' . http_build_query($query_params);
        }
        header("Location: " . $redirect_url);
        exit;
    }

    public function historialCitas() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $empleado_id = $_SESSION['user_id'];
        $pageTitle = "Historial de Citas";
        $active_page = 'historial_citas';
        $contador_notificaciones_no_leidas = 0;
        $filtros = ['fecha_desde' => $_GET['fecha_desde'] ?? null, 'fecha_hasta' => $_GET['fecha_hasta'] ?? null, 'estado' => $_GET['estado'] ?? null];
        $filtros_limpios = array_filter($filtros);
        $citas = [];
        if ($this->citaModel) {
            $citas = $this->citaModel->obtenerHistorialCitasPorEmpleado($empleado_id, $filtros_limpios);
        }
        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }
        $valores_filtros = $filtros;
        $this->renderView('empleado/historial_citas', compact('pageTitle', 'citas', 'active_page', 'valores_filtros', 'contador_notificaciones_no_leidas'));
    }

    public function generarReportes() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $empleado_id = $_SESSION['user_id'];
        $pageTitle = "Reportes de Actividad";
        $active_page = 'generar_reportes';
        $contador_notificaciones_no_leidas = 0;
        $fecha_desde_default = date('Y-m-01');
        $fecha_hasta_default = date('Y-m-t');
        $filtros = ['fecha_desde' => $_GET['fecha_desde'] ?? $fecha_desde_default, 'fecha_hasta' => $_GET['fecha_hasta'] ?? $fecha_hasta_default];
        $validator = new Validator();
        if (method_exists($validator, 'validateDate')) {
            if (!$validator->validateDate($filtros['fecha_desde'])) { $filtros['fecha_desde'] = $fecha_desde_default; $_SESSION['mensaje_temporal_error'] = "Fecha 'Desde' inválida, usando valor por defecto."; }
            if (!$validator->validateDate($filtros['fecha_hasta'])) { $filtros['fecha_hasta'] = $fecha_hasta_default; $_SESSION['mensaje_temporal_error'] = "Fecha 'Hasta' inválida, usando valor por defecto."; }
        } else {
            if (DateTime::createFromFormat('Y-m-d', $filtros['fecha_desde']) === false) { $filtros['fecha_desde'] = $fecha_desde_default; $_SESSION['mensaje_temporal_error'] = "Fecha 'Desde' inválida, usando valor por defecto.";}
            if (DateTime::createFromFormat('Y-m-d', $filtros['fecha_hasta']) === false) { $filtros['fecha_hasta'] = $fecha_hasta_default; $_SESSION['mensaje_temporal_error'] = "Fecha 'Hasta' inválida, usando valor por defecto.";}
        }
        if (strtotime($filtros['fecha_desde']) > strtotime($filtros['fecha_hasta'])) {
            $_SESSION['mensaje_temporal_error'] = "La fecha 'Desde' no puede ser posterior a la fecha 'Hasta'. Se usarán valores por defecto.";
            $filtros['fecha_desde'] = $fecha_desde_default; $filtros['fecha_hasta'] = $fecha_hasta_default;
        }
        $reporte_citas_por_estado = []; $reporte_servicios_realizados = [];
        if ($this->citaModel) {
            if (method_exists($this->citaModel, 'obtenerResumenCitasPorEstadoParaEmpleado')) {
                $reporte_citas_por_estado = $this->citaModel->obtenerResumenCitasPorEstadoParaEmpleado($empleado_id, $filtros['fecha_desde'], $filtros['fecha_hasta']);
            }
            if (method_exists($this->citaModel, 'obtenerResumenServiciosParaEmpleado')) {
                $reporte_servicios_realizados = $this->citaModel->obtenerResumenServiciosParaEmpleado($empleado_id, $filtros['fecha_desde'], $filtros['fecha_hasta']);
            }
        }
        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }
        $valores_filtros = $filtros;
        $this->renderView('empleado/reportes/reportes', compact('pageTitle', 'active_page', 'valores_filtros', 'reporte_citas_por_estado', 'reporte_servicios_realizados', 'contador_notificaciones_no_leidas'));
    }

    public function gestionarHorario() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Gestionar Mi Horario";
        $active_page = 'gestionar_horario_empleado';
        $empleado_id = $_SESSION['user_id'];
        $contador_notificaciones_no_leidas = 0;
        $horariosRecurrentes = []; $excepciones = [];
        if ($this->horarioModel) {
            $horariosRecurrentes = $this->horarioModel->obtenerHorariosRecurrentesPorEmpleado($empleado_id);
            $fechaInicioExcepciones = date('Y-m-d');
            $fechaFinExcepciones = date('Y-m-d', strtotime('+2 months -1 day'));
            $excepciones = $this->horarioModel->obtenerExcepcionesPorEmpleadoYFecha($empleado_id, $fechaInicioExcepciones, $fechaFinExcepciones);
        }
        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }
        $diasSemanaMap = [0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'];
        $this->renderView('empleado/horarios/gestionar_horario', compact('pageTitle', 'active_page', 'horariosRecurrentes', 'excepciones', 'diasSemanaMap', 'contador_notificaciones_no_leidas'));
    }

    public function guardarHorarioRecurrente() {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header("Location: " . BASE_URL . "empleado/gestionar-horario");
            exit;
        }
        $empleado_id = $_SESSION['user_id'];
        $dia_semana = filter_input(INPUT_POST, 'dia_semana', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 6]]);
        $hora_inicio = $_POST['hora_inicio'] ?? null; $hora_fin = $_POST['hora_fin'] ?? null;
        $fecha_desde = !empty($_POST['fecha_desde_recurrente']) ? $_POST['fecha_desde_recurrente'] : null;
        $fecha_hasta = !empty($_POST['fecha_hasta_recurrente']) ? $_POST['fecha_hasta_recurrente'] : null;

        if ($dia_semana === false || $dia_semana === null || !$this->validarFormatoHora($hora_inicio) || !$this->validarFormatoHora($hora_fin)) {
            $_SESSION['mensaje_error_global'] = "Datos de horario recurrente inválidos (día o formato de hora).";
        } elseif (strtotime($hora_inicio) >= strtotime($hora_fin)) {
            $_SESSION['mensaje_error_global'] = "La hora de inicio debe ser anterior a la hora de fin.";
        } elseif (($fecha_desde && !$this->validarFormatoFecha($fecha_desde)) || ($fecha_hasta && !$this->validarFormatoFecha($fecha_hasta))) {
            $_SESSION['mensaje_error_global'] = "Formato de fecha desde/hasta inválido.";
        } elseif ($fecha_desde && $fecha_hasta && strtotime($fecha_desde) > strtotime($fecha_hasta)) {
            $_SESSION['mensaje_error_global'] = "La fecha 'desde' no puede ser posterior a la fecha 'hasta'.";
        } else {
            if ($this->horarioModel) {
                if ($this->horarioModel->crearHorarioRecurrente($empleado_id, $dia_semana, $hora_inicio, $hora_fin, $fecha_desde, $fecha_hasta)) {
                    $_SESSION['mensaje_exito_global'] = "Horario recurrente guardado exitosamente.";
                } else { $_SESSION['mensaje_error_global'] = "Error al guardar el horario recurrente."; }
            } else { $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de horarios no disponible."; }
        }
        header("Location: " . BASE_URL . "empleado/gestionar-horario");
        exit;
    }

    public function eliminarHorarioRecurrente($id_horario_str) {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header("Location: " . BASE_URL . "empleado/gestionar-horario");
            exit;
        }
        $id_horario = filter_var($id_horario_str, FILTER_VALIDATE_INT);
        $empleado_id_actual = $_SESSION['user_id'];
        if ($id_horario && $this->horarioModel) {
            $horario_a_borrar = $this->horarioModel->obtenerHorarioRecurrentePorId($id_horario);
            if ($horario_a_borrar && $horario_a_borrar['empleado_id'] == $empleado_id_actual) {
                if ($this->horarioModel->eliminarHorarioRecurrente($id_horario)) {
                    $_SESSION['mensaje_exito_global'] = "Horario recurrente eliminado exitosamente.";
                } else { $_SESSION['mensaje_error_global'] = "Error al eliminar el horario recurrente."; }
            } else { $_SESSION['mensaje_error_global'] = "No se encontró el horario o no tienes permiso para eliminarlo."; }
        } else { $_SESSION['mensaje_error_global'] = "ID de horario inválido o modelo no disponible."; }
        header("Location: " . BASE_URL . "empleado/gestionar-horario");
        exit;
    }

    public function guardarExcepcionHorario() {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header("Location: " . BASE_URL . "empleado/gestionar-horario");
            exit;
        }
        $empleado_id = $_SESSION['user_id'];
        $fecha = $_POST['fecha_excepcion'] ?? null; $tipo_excepcion = $_POST['tipo_excepcion'] ?? null;
        $hora_inicio_ex = !empty($_POST['hora_inicio_excepcion']) ? $_POST['hora_inicio_excepcion'] : null;
        $hora_fin_ex = !empty($_POST['hora_fin_excepcion']) ? $_POST['hora_fin_excepcion'] : null;
        $descripcion_ex = !empty($_POST['descripcion_excepcion']) ? trim(strip_tags($_POST['descripcion_excepcion'])) : null;

        if (!$this->validarFormatoFecha($fecha) || !in_array($tipo_excepcion, ['NO_DISPONIBLE', 'DISPONIBLE_EXTRA'])) {
            $_SESSION['mensaje_error_global'] = "Datos de excepción inválidos (fecha o tipo).";
        } elseif (($hora_inicio_ex && !$this->validarFormatoHora($hora_inicio_ex)) || ($hora_fin_ex && !$this->validarFormatoHora($hora_fin_ex))) {
            $_SESSION['mensaje_error_global'] = "Formato de hora para excepción inválido.";
        } elseif ($hora_inicio_ex && $hora_fin_ex && strtotime($hora_inicio_ex) >= strtotime($hora_fin_ex)) {
             $_SESSION['mensaje_error_global'] = "La hora de inicio de la excepción no puede ser posterior o igual a la hora de fin.";
        } else {
            if ($this->horarioModel) {
                if ($this->horarioModel->crearExcepcion($empleado_id, $fecha, $tipo_excepcion, $hora_inicio_ex, $hora_fin_ex, $descripcion_ex)) {
                    $_SESSION['mensaje_exito_global'] = "Excepción de horario guardada exitosamente.";
                } else { $_SESSION['mensaje_error_global'] = "Error al guardar la excepción de horario."; }
            } else { $_SESSION['mensaje_error_global'] = "Error del sistema: Modelo de horarios no disponible."; }
        }
        header("Location: " . BASE_URL . "empleado/gestionar-horario");
        exit;
    }

    public function eliminarExcepcionHorario($id_excepcion_str) {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header("Location: " . BASE_URL . "empleado/gestionar-horario");
            exit;
        }
        $id_excepcion = filter_var($id_excepcion_str, FILTER_VALIDATE_INT);
        $empleado_id_actual = $_SESSION['user_id'];
        if ($id_excepcion && $this->horarioModel) {
            $excepcion_a_borrar = $this->horarioModel->obtenerExcepcionPorId($id_excepcion);
            if ($excepcion_a_borrar && $excepcion_a_borrar['empleado_id'] == $empleado_id_actual) {
                if ($this->horarioModel->eliminarExcepcion($id_excepcion)) {
                    $_SESSION['mensaje_exito_global'] = "Excepción de horario eliminada exitosamente.";
                } else { $_SESSION['mensaje_error_global'] = "Error al eliminar la excepción de horario."; }
            } else { $_SESSION['mensaje_error_global'] = "No se encontró la excepción o no tienes permiso para eliminarla."; }
        } else { $_SESSION['mensaje_error_global'] = "ID de excepción inválido o modelo no disponible."; }
        header("Location: " . BASE_URL . "empleado/gestionar-horario");
        exit;
    }

    private function validarFormatoHora($hora) {
        if ($hora === null) return true; // Permitir nulo si es opcional
        return preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $hora) || preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/", $hora);
    }

    private function validarFormatoFecha($fecha) {
        if ($fecha === null) return false; // Asumir que la fecha es requerida si se valida
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    public function verMisHorarios() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Mis Horarios";
        $active_page = 'mis_horarios_empleado';
        $empleado_id = $_SESSION['user_id'];
        $contador_notificaciones_no_leidas = 0;
        $horariosRecurrentes = []; $excepciones = [];
        $diasSemanaMap = [0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'];
        if ($this->horarioModel) {
            $horariosRecurrentes = $this->horarioModel->obtenerHorariosRecurrentesPorEmpleado($empleado_id);
            $fechaInicioExcepciones = date('Y-m-d');
            $fechaFinExcepciones = date('Y-m-d', strtotime('+2 months -1 day')); // Ejemplo: los próximos 2 meses
            $excepciones = $this->horarioModel->obtenerExcepcionesPorEmpleadoYFecha($empleado_id, $fechaInicioExcepciones, $fechaFinExcepciones);
        }
        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }
        $this->renderView('empleado/horarios/mis_horarios', compact('pageTitle', 'active_page', 'horariosRecurrentes', 'excepciones', 'diasSemanaMap', 'contador_notificaciones_no_leidas'));
    }

    // --- Métodos para Facturas ---
    public function listarFacturas() {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
        $pageTitle = "Mis Facturas Generadas";
        $active_page = 'mis_facturas_empleado'; 
        $empleado_id = $_SESSION['user_id'];
        $facturas = [];
        $contador_notificaciones_no_leidas = 0;

        if ($this->facturaModel) {
            $facturas = $this->facturaModel->obtenerFacturasPorEmpleado($empleado_id);
            // error_log("EmpleadoController::listarFacturas - Facturas obtenidas del modelo para empleado ID {$empleado_id}: " . count($facturas) . " facturas.");
        } else {
            $_SESSION['mensaje_error_global'] = "El sistema de facturación no está disponible en este momento.";
            error_log("EmpleadoController::listarFacturas - FacturaModel no está disponible.");
        }
        
        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }

        $this->renderView('empleado/facturas/lista_facturas_empleado', compact('pageTitle', 'active_page', 'facturas', 'contador_notificaciones_no_leidas'));
    }

    public function verFactura($id_factura_str) {
        if (!$this->verificarAccesoEmpleado()) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $id_factura = filter_var($id_factura_str, FILTER_VALIDATE_INT);
        if (!$id_factura) {
            $_SESSION['mensaje_error_global'] = "ID de factura inválido.";
            header("Location: " . BASE_URL . "empleado/facturas");
            exit;
        }

        $pageTitle = "Detalle de Factura";
        $active_page = 'mis_facturas_empleado';
        $empleado_id_sesion = $_SESSION['user_id'];
        $factura = null;
        $contador_notificaciones_no_leidas = 0;

        if ($this->facturaModel && method_exists($this->facturaModel, 'obtenerFacturaPorIdYEmpleado')) {
            $factura = $this->facturaModel->obtenerFacturaPorIdYEmpleado($id_factura, $empleado_id_sesion);
        } else {
            $_SESSION['mensaje_error_global'] = "El sistema de facturación no está disponible o el método para obtener la factura no existe.";
            error_log("EmpleadoController::verFactura - FacturaModel no disponible o método obtenerFacturaPorIdYEmpleado no existe.");
            header("Location: " . BASE_URL . "empleado/facturas");
            exit;
        }

        if (!$factura) {
            $_SESSION['mensaje_error_global'] = "Factura no encontrada o no tienes permiso para verla.";
            header("Location: " . BASE_URL . "empleado/facturas");
            exit;
        }
        
        if ($this->notificacionModel && isset($_SESSION['user_id']) && method_exists($this->notificacionModel, 'contarNoLeidasPorUsuarioId')) {
            $contador_notificaciones_no_leidas = $this->notificacionModel->contarNoLeidasPorUsuarioId($_SESSION['user_id']);
        }

        $this->renderView('empleado/facturas/ver_factura', compact('pageTitle', 'active_page', 'factura', 'contador_notificaciones_no_leidas'));
    }

    public function actualizarEstadoFacturaPost($id_factura_str) {
        if (!$this->verificarAccesoEmpleado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['mensaje_error_global'] = "Acción no permitida.";
            header("Location: " . BASE_URL . "empleado/facturas");
            exit;
        }

        $id_factura = filter_var($id_factura_str, FILTER_VALIDATE_INT);
        $nuevo_estado = $_POST['estado_factura'] ?? null;
        $empleado_id_sesion = $_SESSION['user_id'];

        if (!$id_factura || empty($nuevo_estado)) {
            $_SESSION['mensaje_error_global'] = "Datos incompletos para actualizar el estado de la factura.";
            $redirect_url = $id_factura ? (BASE_URL . "empleado/facturas/ver/" . $id_factura) : (BASE_URL . "empleado/facturas");
            header("Location: " . $redirect_url);
            exit;
        }
        
        // Validar que el nuevo_estado sea uno de los permitidos (opcional pero recomendado)
        $estados_permitidos = ['Pendiente', 'Pagada', 'Anulada'];
        if (!in_array($nuevo_estado, $estados_permitidos)) {
            $_SESSION['mensaje_error_global'] = "Estado de factura no válido.";
            header("Location: " . BASE_URL . "empleado/facturas/ver/" . $id_factura);
            exit;
        }

        if ($this->facturaModel && method_exists($this->facturaModel, 'actualizarEstadoFactura')) {
            if ($this->facturaModel->actualizarEstadoFactura($id_factura, $nuevo_estado, $empleado_id_sesion)) {
                $_SESSION['mensaje_exito_global'] = "Estado de la factura #" . htmlspecialchars($id_factura) . " actualizado a '" . htmlspecialchars($nuevo_estado) . "'.";
            } else {
                $_SESSION['mensaje_error_global'] = "No se pudo actualizar el estado de la factura #" . htmlspecialchars($id_factura) . ". Es posible que el estado ya fuera el mismo, la factura no exista o no tengas permiso.";
            }
        } else {
            $_SESSION['mensaje_error_global'] = "Error del sistema: El modelo de facturación no está disponible para actualizar el estado.";
            error_log("EmpleadoController::actualizarEstadoFacturaPost - FacturaModel no disponible o método actualizarEstadoFactura no existe.");
        }

        header("Location: " . BASE_URL . "empleado/facturas/ver/" . $id_factura); 
        exit;
    }
    // --- Fin Métodos para Facturas ---

    public function __destruct() {
        $this->db = null;
        $this->pdoConn = null;
    }
}
?>