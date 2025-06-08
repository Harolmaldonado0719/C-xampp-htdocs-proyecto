<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen: Construcción de API</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #6dd5ed, #2193b0, #f7971e, #ffd200);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            flex-direction: column;
        }
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }
        .container {
            background: rgba(255,255,255,0.95);
            margin: 40px auto 0 auto;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 16px #00000022;
            max-width: 900px;
        }
        .btns {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn {
            padding: 15px 30px;
            margin: 10px 20px;
            font-size: 18px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(90deg, #007bff 60%, #00c6ff 100%);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 4px 16px #00000022;
            transition: background 0.3s, transform 0.2s;
        }
        .btn:hover {
            background: linear-gradient(90deg, #0056b3 60%, #00aaff 100%);
            transform: scale(1.07);
        }
        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            color: #2193b0;
            margin-top: 30px;
        }
        ul {
            margin-left: 20px;
            line-height: 1.6;
        }
        ul ul {
            margin-left: 20px;
        }
        code {
            background: #f2f2f2;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 15px;
        }
        pre {
            background: #e8e8e8;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            overflow-x: auto;
        }
        footer {
            margin-top: auto;
            padding: 20px 0 10px 0;
            background: rgba(0,0,0,0.15);
            color: #222;
            font-weight: bold;
            letter-spacing: 1px;
            font-size: 16px;
            box-shadow: 0 -2px 8px #00000022;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="btns">
            <a href="dashboard.php"><button class="btn">Atras</button></a>
        </div>
        <h1>Resumen: Construcción de API</h1>
        <p>
            Una <b>API</b> (Interfaz de Programación de Aplicaciones) es un conjunto definido de reglas y protocolos que permiten la comunicación entre diferentes aplicaciones o sistemas. En el contexto del desarrollo web moderno, las APIs, especialmente las <b>APIs REST</b>, son esenciales para conectar de manera eficiente el frontend y el backend, facilitando el intercambio de datos y funcionalidades.
        </p>

        <h2>Conceptos fundamentales:</h2>
        <ul>
            <li><strong>¿Qué es una API?</strong><br>
                Es un intermediario que permite que dos aplicaciones diferentes se comuniquen entre sí sin necesidad de conocer los detalles internos de cada sistema. Por ejemplo, cuando utilizamos una aplicación que muestra mapas, esta puede obtener datos de Google Maps mediante su API, sin que el usuario vea cómo se gestionan esos datos internamente.
            </li>

            <li><strong>REST (Representational State Transfer):</strong><br>
                REST es un estilo arquitectónico para diseñar servicios web que utilizan el protocolo HTTP. Se basa en principios claros para facilitar la comunicación y manipulación de recursos a través de métodos estándar:
                <ul>
                    <li><code>GET</code>: Se utiliza para solicitar y obtener información desde el servidor, por ejemplo, para listar usuarios o productos.</li>
                    <li><code>POST</code>: Sirve para enviar datos y crear nuevos recursos en el servidor, como registrar un nuevo usuario o subir información.</li>
                    <li><code>PUT</code>: Permite actualizar información existente, por ejemplo, modificar los datos de un usuario registrado.</li>
                    <li><code>DELETE</code>: Se usa para eliminar recursos existentes en el servidor, como borrar una cuenta o un artículo.</li>
                </ul>
            </li>

            <li><strong>Creación de endpoints:</strong><br>
                Un endpoint es una URL o ruta específica que representa un recurso en la API. Diseñar endpoints claros y coherentes es fundamental para que los desarrolladores puedan acceder y manipular los datos de forma sencilla y eficiente. Por ejemplo, <code>/api/usuarios</code> podría ser un endpoint para gestionar información de usuarios.
            </li>

            <li><strong>Consumo de APIs:</strong><br>
                El frontend consume las APIs utilizando tecnologías como JavaScript y métodos como <code>fetch</code> o AJAX. Esto permite obtener, enviar o actualizar datos de forma dinámica sin necesidad de recargar la página, mejorando la experiencia del usuario y haciendo las aplicaciones más interactivas.
            </li>

            <li><strong>Seguridad en APIs:</strong><br>
                Es fundamental proteger las APIs para evitar accesos no autorizados y garantizar la integridad de los datos. Se utilizan mecanismos como autenticación mediante tokens, sesiones, OAuth, y permisos que definen qué acciones puede realizar cada usuario o aplicación. Además, se recomienda validar y sanitizar todos los datos recibidos.
            </li>

            <li><strong>Pruebas de API:</strong><br>
                Para asegurar que los endpoints funcionan correctamente, se utilizan herramientas especializadas como <b>Postman</b> o Swagger. Estas permiten enviar solicitudes de prueba, validar respuestas y detectar errores antes de integrar la API en un entorno productivo.
            </li>

            <li><strong>Documentación:</strong><br>
                La documentación es clave para que otros desarrolladores comprendan cómo usar la API. Debe incluir detalles de cada endpoint, los parámetros que acepta, ejemplos de solicitudes y respuestas, códigos de estado HTTP, y cualquier restricción o requisito especial.
            </li>
        </ul>

        <h2>Buenas prácticas para el desarrollo de APIs:</h2>
        <ul>
            <li><b>Seguridad:</b> Utilizar sentencias preparadas y evitar concatenaciones directas en consultas SQL para prevenir ataques de inyección.</li>
            <li><b>Validación:</b> Validar y sanitizar toda la información recibida para proteger la integridad de la aplicación y evitar datos corruptos o maliciosos.</li>
            <li><b>Formato estándar:</b> Utilizar formatos claros y universales como JSON para facilitar la comunicación entre sistemas heterogéneos.</li>
            <li><b>Documentación constante:</b> Mantener la documentación actualizada para facilitar el mantenimiento, escalabilidad y la integración con otros servicios.</li>
            <li><b>Manejo de errores:</b> Implementar respuestas claras y controladas en caso de fallos, devolviendo códigos HTTP apropiados y mensajes descriptivos para que los clientes de la API puedan reaccionar adecuadamente.</li>
        </ul>

        <h2>Ejemplo básico de endpoints REST:</h2>
        <pre>
GET    /api/usuarios        - Obtiene la lista de todos los usuarios.
POST   /api/usuarios        - Crea un nuevo usuario con los datos enviados.
PUT    /api/usuarios/{id}   - Actualiza los datos del usuario con el ID especificado.
DELETE /api/usuarios/{id}   - Elimina el usuario con el ID especificado.
        </pre>
    </div>
    <footer>
        &copy; Harol Maldonado 2025
    </footer>
</body>
</html>
