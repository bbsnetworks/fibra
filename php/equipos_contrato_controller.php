<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'conexion_smartgatepos.php';

/*
|--------------------------------------------------------------------------
| CONEXIÓN
|--------------------------------------------------------------------------
| Ajusta esto solo si tu archivo conexion.php usa otro nombre.
| Soporta $conexion o $conn.
*/
$db = null;

if (isset($conexion_pos) && $conexion_pos instanceof mysqli) {
    $db = $conexion_pos;
}

if (!$db) {
    responder(false, 'No se encontró conexión válida a la base de datos smartgatepos.');
}

$db->set_charset('utf8mb4');


/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function responder($ok, $message = '', $extra = [])
{
    echo json_encode(array_merge([
        'ok' => $ok,
        'message' => $message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function obtenerUsuarioSesion()
{
    $usuario = $_SESSION['usuario'] ?? [];

    $id = $usuario['id'] 
        ?? $_SESSION['iduser'] 
        ?? $_SESSION['idusuario'] 
        ?? null;

    $nombre = $usuario['nombre'] 
        ?? $_SESSION['username'] 
        ?? $_SESSION['nombre'] 
        ?? 'Sistema';

    $rol = $usuario['rol'] 
        ?? $_SESSION['rol'] 
        ?? $_SESSION['tipo'] 
        ?? 'worker';

    return [
        'id' => $id ? (int)$id : null,
        'nombre' => $nombre,
        'rol' => strtolower(trim($rol))
    ];
}

function leerJsonBody()
{
    $raw = file_get_contents('php://input');
    if (!$raw) return [];

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function limpiarDecimal($valor)
{
    if ($valor === null || $valor === '') return 0;
    $valor = str_replace(['$', ',', ' '], '', $valor);
    return is_numeric($valor) ? (float)$valor : 0;
}

function generarVentaIdUnico(mysqli $db)
{
    do {
        $ventaId = 'CTR-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $stmt = $db->prepare("SELECT COUNT(*) AS total FROM pagos_productos WHERE venta_id = ?");
        $stmt->bind_param('s', $ventaId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $existe = (int)($res['total'] ?? 0) > 0;
    } while ($existe);

    return $ventaId;
}

function sincronizarStockProducto(mysqli $db, int $productoId)
{
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(stock), 0) AS stock_total
        FROM inventario_usuarios
        WHERE producto_id = ?
          AND activo = 1
    ");
    $stmt->bind_param('i', $productoId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stockTotal = (int)($row['stock_total'] ?? 0);

    $stmt = $db->prepare("
        UPDATE productos
        SET stock = ?
        WHERE id = ?
    ");
    $stmt->bind_param('ii', $stockTotal, $productoId);
    $stmt->execute();
    $stmt->close();
}

function normalizarEquipos($equipos)
{
    if (is_string($equipos)) {
        $decoded = json_decode($equipos, true);
        $equipos = is_array($decoded) ? $decoded : [];
    }

    if (!is_array($equipos)) return [];

    $agrupados = [];

    foreach ($equipos as $item) {
        $inventarioId = (int)($item['inventario_usuario_id'] ?? 0);
        $cantidad = (int)($item['cantidad'] ?? 1);

        if ($inventarioId <= 0) continue;
        if ($cantidad <= 0) $cantidad = 1;

        if (!isset($agrupados[$inventarioId])) {
            $agrupados[$inventarioId] = [
                'inventario_usuario_id' => $inventarioId,
                'cantidad' => 0
            ];
        }

        $agrupados[$inventarioId]['cantidad'] += $cantidad;
    }

    return array_values($agrupados);
}

/*
|--------------------------------------------------------------------------
| FUNCIÓN REUTILIZABLE PARA guardar_contrato.php
|--------------------------------------------------------------------------
| Puedes incluir este archivo desde guardar_contrato.php y llamar esta función
| dentro de la misma transacción si quieres hacerlo más seguro.
*/

function registrarVentaEquiposContrato(
    mysqli $db,
    array $equipos,
    array $opciones = []
) {
    $equipos = normalizarEquipos($equipos);

    if (empty($equipos)) {
        return [
            'ok' => true,
            'venta_id' => null,
            'productos' => [],
            'message' => 'No se enviaron equipos de inventario.'
        ];
    }

    $usuarioId = $opciones['usuario_id'] ?? null;
    $metodoPago = $opciones['metodo_pago'] ?? 'Contrato';
    $observaciones = $opciones['observaciones'] ?? 'Equipo usado en contrato';
    $ventaId = $opciones['venta_id'] ?? generarVentaIdUnico($db);

    $productosInsertados = [];

    foreach ($equipos as $equipo) {
        $inventarioUsuarioId = (int)$equipo['inventario_usuario_id'];
        $cantidad = (int)$equipo['cantidad'];

        if ($cantidad <= 0) {
            throw new Exception('La cantidad del equipo debe ser mayor a 0.');
        }

        /*
        |--------------------------------------------------------------------------
        | Bloqueamos el inventario para evitar doble descuento
        |--------------------------------------------------------------------------
        */
        $stmt = $db->prepare("
            SELECT 
                iu.id AS inventario_usuario_id,
                iu.producto_id,
                iu.usuario_id AS usuario_propietario_id,
                iu.precio_proveedor,
                iu.precio_venta,
                iu.stock,
                iu.activo,

                p.codigo,
                p.marca,
                p.modelo,
                p.descripcion,
                p.precio AS precio_producto,
                p.precio_proveedor AS costo_producto
            FROM inventario_usuarios iu
            INNER JOIN productos p ON p.id = iu.producto_id
            WHERE iu.id = ?
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->bind_param('i', $inventarioUsuarioId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            throw new Exception("No se encontró el inventario seleccionado ID {$inventarioUsuarioId}.");
        }

        if ((int)$row['activo'] !== 1) {
            throw new Exception("El inventario del producto {$row['descripcion']} no está activo.");
        }

        $stockActual = (int)$row['stock'];

        if ($stockActual < $cantidad) {
            $nombreEquipo = trim(($row['marca'] ?? '') . ' ' . ($row['modelo'] ?? ''));
            if ($nombreEquipo === '') {
                $nombreEquipo = $row['descripcion'] ?? 'Producto';
            }

            throw new Exception("Stock insuficiente para {$nombreEquipo}. Disponible: {$stockActual}, solicitado: {$cantidad}.");
        }

        $productoId = (int)$row['producto_id'];
        $usuarioPropietarioId = (int)$row['usuario_propietario_id'];

        $precioUnitario = limpiarDecimal($row['precio_venta']);
        if ($precioUnitario <= 0) {
            $precioUnitario = limpiarDecimal($row['precio_producto']);
        }

        $costoUnitario = limpiarDecimal($row['precio_proveedor']);
        if ($costoUnitario <= 0) {
            $costoUnitario = limpiarDecimal($row['costo_producto']);
        }

        $total = $precioUnitario * $cantidad;
        $utilidadTotal = ($precioUnitario - $costoUnitario) * $cantidad;

        /*
        |--------------------------------------------------------------------------
        | Descontar stock de inventario_usuario
        |--------------------------------------------------------------------------
        */
        $nuevoStock = $stockActual - $cantidad;

        $stmt = $db->prepare("
            UPDATE inventario_usuarios
            SET stock = ?
            WHERE id = ?
        ");
        $stmt->bind_param('ii', $nuevoStock, $inventarioUsuarioId);
        $stmt->execute();

        if ($stmt->affected_rows < 0) {
            $stmt->close();
            throw new Exception('No se pudo actualizar el stock del inventario.');
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | Insertar venta en pagos_productos
        |--------------------------------------------------------------------------
        */
        $stmt = $db->prepare("
            INSERT INTO pagos_productos (
                producto_id,
                inventario_usuario_id,
                cantidad,
                precio_unitario,
                costo_unitario,
                total,
                utilidad_total,
                metodo_pago,
                fecha_pago,
                usuario_id,
                usuario_propietario_id,
                observaciones,
                venta_id
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?
            )
        ");

        $stmt->bind_param(
            'iiidddssiiss',
            $productoId,
            $inventarioUsuarioId,
            $cantidad,
            $precioUnitario,
            $costoUnitario,
            $total,
            $utilidadTotal,
            $metodoPago,
            $usuarioId,
            $usuarioPropietarioId,
            $observaciones,
            $ventaId
        );

        $stmt->execute();
        $pagoProductoId = $stmt->insert_id;
        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | Sincronizar productos.stock
        |--------------------------------------------------------------------------
        */
        sincronizarStockProducto($db, $productoId);

        $productosInsertados[] = [
            'pago_producto_id' => $pagoProductoId,
            'producto_id' => $productoId,
            'inventario_usuario_id' => $inventarioUsuarioId,
            'usuario_propietario_id' => $usuarioPropietarioId,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
            'utilidad_total' => $utilidadTotal,
            'stock_restante' => $nuevoStock,
            'marca' => $row['marca'] ?? '',
            'modelo' => $row['modelo'] ?? '',
            'descripcion' => $row['descripcion'] ?? ''
        ];
    }

    return [
        'ok' => true,
        'venta_id' => $ventaId,
        'productos' => $productosInsertados,
        'message' => 'Venta de equipos registrada correctamente.'
    ];
}

/*
|--------------------------------------------------------------------------
| ACCIONES DEL CONTROLLER
|--------------------------------------------------------------------------
*/

$usuarioSesion = obtenerUsuarioSesion();

$accion = $_GET['accion'] 
    ?? $_POST['accion'] 
    ?? null;

$body = leerJsonBody();

if (!$accion && isset($body['accion'])) {
    $accion = $body['accion'];
}

if (!$accion) {
    responder(false, 'No se recibió ninguna acción.');
}

/*
|--------------------------------------------------------------------------
| BUSCAR EQUIPOS
|--------------------------------------------------------------------------
| URL:
| equipos_contrato_controller.php?accion=buscar&q=router
|--------------------------------------------------------------------------
*/

if ($accion === 'buscar') {
    $q = trim($_GET['q'] ?? $_GET['busqueda'] ?? '');
    $limite = (int)($_GET['limite'] ?? 20);

    if ($limite <= 0 || $limite > 50) {
        $limite = 20;
    }

    $where = "
        WHERE iu.activo = 1
          AND iu.stock > 0
    ";

    $params = [];
    $types = '';

    /*
    |--------------------------------------------------------------------------
    | Si el usuario es worker, solo ve su inventario.
    | Admin/root ven todo.
    |--------------------------------------------------------------------------
    */
    $rol = $usuarioSesion['rol'];
    $usuarioIdSesion = $usuarioSesion['id'];

    if ($rol === 'worker' || $rol === 'pagos') {
        if (!$usuarioIdSesion) {
            responder(false, 'No se pudo identificar el usuario de sesión.');
        }

        $where .= " AND iu.usuario_id = ? ";
        $params[] = $usuarioIdSesion;
        $types .= 'i';
    }

    if ($q !== '') {
        $like = '%' . $q . '%';

        $where .= "
            AND (
                p.codigo LIKE ?
                OR p.marca LIKE ?
                OR p.modelo LIKE ?
                OR p.descripcion LIKE ?
                OR CONCAT_WS(' ', p.marca, p.modelo) LIKE ?
                OR u.nombre LIKE ?
            )
        ";

        for ($i = 0; $i < 6; $i++) {
            $params[] = $like;
            $types .= 's';
        }
    }

    $sql = "
        SELECT
            iu.id AS inventario_usuario_id,
            iu.producto_id,
            iu.usuario_id AS usuario_propietario_id,
            COALESCE(u.nombre, 'Sin propietario') AS propietario,

            p.codigo,
            COALESCE(p.marca, '') AS marca,
            COALESCE(p.modelo, '') AS modelo,
            p.descripcion,

            iu.precio_proveedor,
            iu.precio_venta,
            iu.stock,

            p.precio AS precio_producto,
            p.precio_proveedor AS costo_producto,
            p.categoria_id
        FROM inventario_usuarios iu
        INNER JOIN productos p ON p.id = iu.producto_id
        LEFT JOIN usuarios u ON u.id = iu.usuario_id
        $where
        ORDER BY p.marca ASC, p.modelo ASC, p.descripcion ASC
        LIMIT ?
    ";

    $params[] = $limite;
    $types .= 'i';

    $stmt = $db->prepare($sql);

    if (!$stmt) {
        responder(false, 'Error preparando búsqueda: ' . $db->error);
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $res = $stmt->get_result();
    $productos = [];

    while ($row = $res->fetch_assoc()) {
        $precioVenta = limpiarDecimal($row['precio_venta']);
        if ($precioVenta <= 0) {
            $precioVenta = limpiarDecimal($row['precio_producto']);
        }

        $precioProveedor = limpiarDecimal($row['precio_proveedor']);
        if ($precioProveedor <= 0) {
            $precioProveedor = limpiarDecimal($row['costo_producto']);
        }

        $nombre = trim(($row['marca'] ?? '') . ' ' . ($row['modelo'] ?? ''));
        if ($nombre === '') {
            $nombre = $row['descripcion'] ?? 'Producto sin nombre';
        }

        $productos[] = [
            'inventario_usuario_id' => (int)$row['inventario_usuario_id'],
            'producto_id' => (int)$row['producto_id'],
            'usuario_propietario_id' => (int)$row['usuario_propietario_id'],
            'propietario' => $row['propietario'],

            'codigo' => $row['codigo'],
            'marca' => $row['marca'],
            'modelo' => $row['modelo'],
            'nombre' => $nombre,
            'descripcion' => $row['descripcion'],

            'precio_venta' => $precioVenta,
            'precio_proveedor' => $precioProveedor,
            'stock' => (int)$row['stock'],
            'categoria_id' => $row['categoria_id'] ? (int)$row['categoria_id'] : null
        ];
    }

    $stmt->close();

    responder(true, 'Productos encontrados.', [
        'productos' => $productos
    ]);
}

/*
|--------------------------------------------------------------------------
| VALIDAR STOCK
|--------------------------------------------------------------------------
| POST JSON:
| {
|   "accion": "validar_stock",
|   "equipos": [
|     {"inventario_usuario_id": 1, "cantidad": 2}
|   ]
| }
|--------------------------------------------------------------------------
*/

if ($accion === 'validar_stock') {
    $equipos = $body['equipos'] 
        ?? $_POST['equipos'] 
        ?? [];

    $equipos = normalizarEquipos($equipos);

    if (empty($equipos)) {
        responder(true, 'No hay equipos de inventario para validar.', [
            'valido' => true
        ]);
    }

    $errores = [];
    $detalle = [];

    foreach ($equipos as $equipo) {
        $inventarioUsuarioId = (int)$equipo['inventario_usuario_id'];
        $cantidad = (int)$equipo['cantidad'];

        $stmt = $db->prepare("
            SELECT 
                iu.id,
                iu.stock,
                iu.activo,
                p.marca,
                p.modelo,
                p.descripcion
            FROM inventario_usuarios iu
            INNER JOIN productos p ON p.id = iu.producto_id
            WHERE iu.id = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $inventarioUsuarioId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $errores[] = "No existe el inventario ID {$inventarioUsuarioId}.";
            continue;
        }

        $nombre = trim(($row['marca'] ?? '') . ' ' . ($row['modelo'] ?? ''));
        if ($nombre === '') {
            $nombre = $row['descripcion'] ?? 'Producto';
        }

        $stock = (int)$row['stock'];
        $activo = (int)$row['activo'];

        $detalle[] = [
            'inventario_usuario_id' => $inventarioUsuarioId,
            'nombre' => $nombre,
            'cantidad_solicitada' => $cantidad,
            'stock_disponible' => $stock,
            'activo' => $activo
        ];

        if ($activo !== 1) {
            $errores[] = "{$nombre} no está activo.";
        }

        if ($stock < $cantidad) {
            $errores[] = "{$nombre} tiene stock insuficiente. Disponible: {$stock}, solicitado: {$cantidad}.";
        }
    }

    responder(empty($errores), empty($errores) ? 'Stock disponible.' : 'Hay problemas de stock.', [
        'valido' => empty($errores),
        'errores' => $errores,
        'detalle' => $detalle
    ]);
}

/*
|--------------------------------------------------------------------------
| REGISTRAR VENTA DE EQUIPOS PARA CONTRATO
|--------------------------------------------------------------------------
| POST JSON:
| {
|   "accion": "registrar_venta_contrato",
|   "equipos": [
|     {"inventario_usuario_id": 1, "cantidad": 1}
|   ],
|   "ncontrato": "123",
|   "cliente": "Juan Pérez",
|   "metodo_pago": "Contrato"
| }
|--------------------------------------------------------------------------
*/

if ($accion === 'registrar_venta_contrato') {
    $equipos = $body['equipos'] 
        ?? $_POST['equipos'] 
        ?? [];

    $ncontrato = trim($body['ncontrato'] ?? $_POST['ncontrato'] ?? '');
    $cliente = trim($body['cliente'] ?? $_POST['cliente'] ?? '');
    $metodoPago = trim($body['metodo_pago'] ?? $_POST['metodo_pago'] ?? 'Contrato');

    $equipos = normalizarEquipos($equipos);

    if (empty($equipos)) {
        responder(true, 'No se registró venta porque no se seleccionaron equipos del inventario.', [
            'venta_id' => null,
            'productos' => []
        ]);
    }

    $usuarioId = $usuarioSesion['id'];

    if (!$usuarioId) {
        responder(false, 'No se pudo identificar el usuario que registra la venta.');
    }

    $observaciones = 'Equipo usado en contrato';

    if ($ncontrato !== '') {
        $observaciones .= " #{$ncontrato}";
    }

    if ($cliente !== '') {
        $observaciones .= " / Cliente: {$cliente}";
    }

    try {
        $db->begin_transaction();

        $resultado = registrarVentaEquiposContrato($db, $equipos, [
            'usuario_id' => $usuarioId,
            'metodo_pago' => $metodoPago ?: 'Contrato',
            'observaciones' => $observaciones
        ]);

        $db->commit();

        responder(true, 'Venta de equipos registrada correctamente.', $resultado);
    } catch (Throwable $e) {
        $db->rollback();

        responder(false, $e->getMessage());
    }
}

responder(false, 'Acción no válida.');