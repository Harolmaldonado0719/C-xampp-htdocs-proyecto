<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/ProductoModel.php'; 

class ProductoController {

    private $db;
    private $pdoConn; 
    private $productoModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $this->db = new Database();
            $this->pdoConn = $this->db->getPdoConnection(); 
            if (!$this->pdoConn) {
                throw new Exception("ProductoController: No se pudo obtener la conexión PDO.");
            }
            $this->productoModel = new ProductoModel($this->pdoConn); 
        } catch (Exception $e) {
            error_log("ProductoController Constructor: " . $e->getMessage());
            $_SESSION['mensaje_error_global'] = "Error crítico del sistema (ProdInit). Por favor, intente más tarde.";
            
            if(defined('DEBUG_MODE') && DEBUG_MODE){ 
                die("Error crítico del sistema al inicializar ProductoController: " . $e->getMessage() . ". Revise los logs.");
            } else {
                die("Error crítico del sistema. Por favor, contacte al administrador. (Ref: ProdCtrlInit)");
            }
        }
    }

    public function mostrarCatalogoCliente() {
        $pageTitle = "Catálogo de Productos";
        $productos = []; 

        if ($this->productoModel) {
            $productos = $this->productoModel->obtenerTodosConCategoria(); 
        } else {
            $_SESSION['mensaje_error_global'] = "Error al cargar los productos: Modelo no disponible.";
        }
        
        foreach ($productos as &$producto_item) { 
            if (!empty($producto_item['imagen_url'])) {
                $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                $producto_item['imagen_completa_url'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($producto_item['imagen_url']);
            } else {
                $producto_item['imagen_completa_url'] = BASE_URL . 'img/placeholder_producto.png'; 
            }
        }
        unset($producto_item); 

        $viewPath = dirname(__DIR__) . '/Views/cliente/productos/catalogo.php'; 
        
        $data_for_view = compact('pageTitle', 'productos', 'viewPath');
        extract($data_for_view);
        
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            if(file_exists($viewPath)){
                include $viewPath;
            } else {
                echo "Error: No se pudo encontrar la vista del catálogo ('{$viewPath}') ni el layout principal.";
                error_log("Error en ProductoController: Vista no encontrada en " . $viewPath);
            }
        }
    }

    public function verDetalleProductoCliente($idProducto_str) {
        $idProducto = filter_var($idProducto_str, FILTER_VALIDATE_INT);
        if ($idProducto === false || $idProducto <= 0) {
            $_SESSION['mensaje_error_global'] = "ID de producto inválido.";
            http_response_code(400); // Bad Request
            $pageTitle = "Producto Inválido";
            $viewPath = dirname(__DIR__) . '/Views/errors/400.php'; // o una vista de error genérica
            // Considerar redirigir si es más apropiado para la UX
            // if(!headers_sent()){ header('Location: ' . BASE_URL . 'productos'); exit;}
        } else {
            $producto = null; 
            $pageTitle = "Detalle del Producto"; 

            if ($this->productoModel) {
                $producto = $this->productoModel->obtenerPorIdConCategoria($idProducto); 
            } else {
                 $_SESSION['mensaje_error_global'] = "Error al cargar el producto: Modelo no disponible.";
            }

            if (!$producto) {
                $_SESSION['mensaje_error_global'] = "Producto no encontrado o no disponible.";
                http_response_code(404);
                $pageTitle = "Producto no encontrado";
                $viewPath = dirname(__DIR__) . '/Views/errors/404.php'; 
            } else {
                $pageTitle = htmlspecialchars($producto['nombre']); 
                if (!empty($producto['imagen_url'])) {
                    $uploadPath = defined('APP_UPLOAD_DIR_PUBLIC_PATH') ? APP_UPLOAD_DIR_PUBLIC_PATH : 'uploads/';
                    $producto['imagen_completa_url'] = BASE_URL . rtrim($uploadPath, '/') . '/' . htmlspecialchars($producto['imagen_url']);
                } else {
                    $producto['imagen_completa_url'] = BASE_URL . 'img/placeholder_producto.png';
                }
                $viewPath = dirname(__DIR__) . '/Views/cliente/productos/detalle_producto.php';
            }
        }
        
        $data_for_view = compact('pageTitle', 'producto', 'viewPath'); // 'producto' puede ser null
        extract($data_for_view);
        
        if (file_exists(dirname(__DIR__) . '/Views/layouts/main_layout.php')) {
            include dirname(__DIR__) . '/Views/layouts/main_layout.php';
        } else {
            if(file_exists($viewPath)){ // $viewPath ya está definida arriba para error o éxito
                include $viewPath;
            } else {
                echo "Error: No se pudo encontrar la vista ('{$viewPath}') ni el layout principal.";
                error_log("Error en ProductoController: Vista no encontrada en " . $viewPath);
            }
        }
    }

    public function __destruct() {
        // El destructor de Database se encargará de cerrar la conexión
        $this->db = null;
        $this->pdoConn = null;
    }
}
?>