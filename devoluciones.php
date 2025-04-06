<?php
include 'db.php';

// Obtener lista de trabajos con nro_orden
$trabajos = $conn->query("SELECT idtrabajo, nro_orden FROM trabajo ORDER BY idtrabajo DESC");
?>

<?php
session_start(); // Iniciar la sesión

// Verificar si el usuario NO está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redirige al login
    exit(); // Detiene la ejecución del script
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ordenes de Compra</title>
<!-- ✅ Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ✅ DataTables CSS (FALTABA) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<!-- ✅ Select2 CSS (FALTABA EN TU CÓDIGO) -->
<!-- ✅ Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<!-- ✅ Tu estilo personalizado -->
<link rel="stylesheet" href="css/style2.css">




</head>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Devoluciones</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">

                <!-- ✅ Dropdown Productos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="index.php" id="inventarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Productos
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="inventarioDropdown">
                        <li><a class="dropdown-item" href="index.php">Inventario</a></li>
                        <li><a class="dropdown-item" href="activos.php">Activos Físicos</a></li>
                    </ul>
                </li>

                <!-- ✅ Dropdown Trabajos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="trabajosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Trabajos
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="trabajosDropdown">
                        <li><a class="dropdown-item" href="trabajos.php">Ver Trabajos</a></li>
                        <li><a class="dropdown-item" href="clientes.php">Clientes</a></li>
                        <li><a class="dropdown-item" href="devoluciones.php">Devoluciones</a></li>
                        <li><a class="dropdown-item" href="ordenesCompra.php">Ordenes Compra</a></li>

                    </ul>
                </li>

                  <!-- ✅ Dropdown Personal -->
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" id="personalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          Personal
                      </a>
                      <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="personalDropdown">
                          <li><a class="dropdown-item" href="empleados.php">Empleados</a></li>
                          <li><a class="dropdown-item" href="usuarios.php">Usuarios</a></li>
                      </ul>
                  </li>
                <!-- 🔹 Resto del menú -->
                <li class="nav-item"><a class="nav-link" href="vehiculos.php">Vehículos</a></li>
                <li class="nav-item"><a class="nav-link" href="sucursal.php">Sucursales</a></li>
                <li class="nav-item"><a class="nav-link" href="documentos.php">Documentos</a></li>
                <li class="nav-item"><a class="nav-link" href="historial.php">Historial</a></li>
                
            </ul>

            <div class="logout-container ms-auto">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php
// ✅ Asegurarse de que $condicion esté definida
$condicion = ""; // Puedes reemplazarlo con condiciones dinámicas más adelante si lo necesitas

// Primero calculamos los totales ANTES de mostrar el resumen
$sql_totales = "
    SELECT 
        SUM(d.devueltos * p.precio) AS total_recuperado,
        SUM(d.usados * p.precio) AS total_perdido
    FROM devoluciones d
    JOIN trabajo_producto tp ON d.trabajo_producto_id = tp.idtrabajo_producto
    JOIN producto p ON tp.producto_id = p.idproducto
    $condicion
";

$result_totales = $conn->query($sql_totales);
$totales = $result_totales->fetch_assoc();

// Asignamos valores por defecto si no hay resultados
$total_recuperado = $totales['total_recuperado'] ?? 0;
$total_perdido = $totales['total_perdido'] ?? 0;
?>
<!-- Resumen financiero (compacto y angosto) -->
<div class="row justify-content-center mb-3">
    <div class="col-sm-6 col-md-5 col-lg-4 mb-2">
        <div class="card border-success shadow-sm h-100">
            <div class="card-header bg-success text-white py-1">
                <h6 class="mb-0">💰 Total Recuperado</h6>
            </div>
            <div class="card-body text-center py-2">
                <h5 class="text-success mb-1">$<?= number_format($total_recuperado, 0, '', '.') ?></h5>
                <small class="text-muted">Productos devueltos</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-5 col-lg-4 mb-2">
        <div class="card border-danger shadow-sm h-100">
            <div class="card-header bg-danger text-white py-1">
                <h6 class="mb-0">💸 Total Perdido</h6>
            </div>
            <div class="card-body text-center py-2">
                <h5 class="text-danger mb-1">$<?= number_format($total_perdido, 0, '', '.') ?></h5>
                <small class="text-muted">Usados o perdidos</small>
            </div>
        </div>
    </div>
</div>




<div class="container mt-4">
    <h4>🔄 Visualizar Devoluciones por Trabajo</h4>

    <!-- 🔽 Selector de trabajo -->
    <form method="GET" class="mb-3">
        <label for="trabajo_id">Selecciona un trabajo:</label>
        <select name="trabajo_id" id="trabajo_id" style="width: 300px;"> 
        <option value="">-- Ver todos --</option>
            <?php while ($t = $trabajos->fetch_assoc()): ?>
                <option value="<?= $t['idtrabajo'] ?>" <?= (isset($_GET['trabajo_id']) && $_GET['trabajo_id'] == $t['idtrabajo']) ? 'selected' : '' ?>>
                    #<?= $t['idtrabajo'] ?> - <?= htmlspecialchars($t['nro_orden']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button class="btn btn-primary ms-2" type="submit">Buscar</button>
    </form>

<?php
$condicion = "";
if (isset($_GET['trabajo_id']) && is_numeric($_GET['trabajo_id'])) {
    $trabajo_id = intval($_GET['trabajo_id']);
    $condicion = "WHERE d.trabajo_id = $trabajo_id";
}

// Consulta de devoluciones
$sql = "
    SELECT 
        d.trabajo_id,
        t.nro_orden,
        p.nombre AS producto,
        p.precio,
        tp.cantidad AS asignado,
        d.devueltos,
        d.usados,
        d.fecha
    FROM devoluciones d
    JOIN trabajo_producto tp ON d.trabajo_producto_id = tp.idtrabajo_producto
    JOIN producto p ON tp.producto_id = p.idproducto
    JOIN trabajo t ON d.trabajo_id = t.idtrabajo
    $condicion
    ORDER BY d.fecha DESC
";

$result = $conn->query($sql);
?>

<?php if ($result->num_rows > 0): ?>
    <table id="tablaDevoluciones" class="table table-bordered table-hover mt-3">
    <thead class="table-dark">
            <tr>
                <th>ID Trabajo</th>
                <th>N° Orden</th>
                <th>Producto</th>
                <th>Precio Unitario</th>
                <th>Asignado</th>
                <th>Devueltos</th>
                <th>Valor Devuelto</th>
                <th>Usados/Perdidos</th>
                <th>Valor Perdido</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $valor_devuelto = $row['devueltos'] * $row['precio'];
                $valor_perdido = $row['usados'] * $row['precio'];
                ?>
                <tr>
                    <td>#<?= $row['trabajo_id'] ?></td>
                    <td><?= htmlspecialchars($row['nro_orden']) ?></td>
                    <td><?= htmlspecialchars($row['producto']) ?></td>
                    <td>$<?= number_format($row['precio'], 0, '', '') ?></td>
                    <td><?= $row['asignado'] ?></td>
                    <td><?= $row['devueltos'] ?></td>
                    <td>$<?= number_format($valor_devuelto, 0, '', '') ?></td>
                    <td><?= $row['usados'] ?></td>
                    <td>$<?= number_format($valor_perdido, 0, '', '') ?></td>
                    <td><?= date("d-m-Y H:i", strtotime($row['fecha'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">No hay devoluciones registradas<?= isset($trabajo_id) ? " para este trabajo." : "." ?></div>
<?php endif; ?>
</div>


<!-- ✅ jQuery debe ir primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ Luego Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- ✅ Luego Bootstrap (opcional para JS) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ Luego DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Tu código personalizado -->
<script>
$(document).ready(function () {
    $('#tablaDevoluciones').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        },
        pageLength: 100
    });

    $('#trabajo_id').select2({
        placeholder: "Buscar por ID o N° de Orden",
        allowClear: true,
        width: 'resolve' // <-- aquí está el truco
    });
});
</script>


</body>
</html>