<?php
include 'db.php';
session_start();

// Si no está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Si no tiene rol de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    // Opcional: mensaje de acceso denegado
    echo "<script>
        alert('Acceso denegado: esta página es solo para administradores.');
        window.location.href = 'index.php';
    </script>";
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- SweetAlert2 CSS (opcional pero recomendado) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

</head>
<body>


<?php
if (isset($_SESSION['mensaje'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '{$_SESSION['mensaje']['tipo']}',
                title: '{$_SESSION['mensaje']['titulo']}',
                text: '{$_SESSION['mensaje']['texto']}'
            });
        });
    </script>";
    unset($_SESSION['mensaje']); // 👈 Esto es clave para que no se repita
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Documentos</a>
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





<div class="container mt-5">
    <div class="text-center mb-3">
        <h2 class="mb-3">Lista de Usuarios</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
            ➕ Agregar Usuario
        </button>
    </div>

    <table id="tablaUsuarios" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT * FROM usuario ORDER BY idusuario ASC";
            $usuarios = mysqli_query($conn, $query);
            while ($u = mysqli_fetch_assoc($usuarios)) {
                echo "<tr>
                    <td>{$u['idusuario']}</td>
                    <td>{$u['nombre']}</td>
                    <td>{$u['email']}</td>
                    <td>{$u['rol']}</td>
                    <td>
                        <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#modalEditar{$u['idusuario']}'>Editar</button>
                        <a href='eliminar_usuario.php?id={$u['idusuario']}' class='btn btn-sm btn-danger' onclick=\"return confirm('¿Eliminar este usuario?')\">Eliminar</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
mysqli_data_seek($usuarios, 0); // Reiniciar el puntero

while ($u = mysqli_fetch_assoc($usuarios)) {
    echo "
    <!-- Modal Editar Usuario -->
    <div class='modal fade' id='modalEditar{$u['idusuario']}' tabindex='-1' aria-labelledby='modalEditarLabel{$u['idusuario']}' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <form action='editar_usuario.php' method='POST'>
                    <input type='hidden' name='idusuario' value='{$u['idusuario']}'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='modalEditarLabel{$u['idusuario']}'>Editar Usuario</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                    </div>
                    <div class='modal-body'>

                        <div class='mb-3'>
                            <label class='form-label'>Nombre:</label>
                            <input type='text' name='nombre' class='form-control' value='{$u['nombre']}' required>
                        </div>

                        <div class='mb-3'>
                            <label class='form-label'>Email:</label>
                            <input type='email' name='email' class='form-control' value='{$u['email']}' required>
                        </div>

                        <div class='mb-3'>
                            <label class='form-label'>Rol:</label>
                            <select name='rol' class='form-select' required>
                                <option value='Administrador' " . ($u['rol'] === 'Administrador' ? 'selected' : '') . ">Administrador</option>
                                <option value='Usuario' " . ($u['rol'] === 'Usuario' ? 'selected' : '') . ">Usuario</option>
                            </select>
                        </div>

                        <div class='mb-3'>
                            <label class='form-label'>Nueva Contraseña (opcional):</label>
                            <input type='password' name='nueva_contrasena' class='form-control' placeholder='Dejar en blanco para no cambiar'>
                        </div>

                    </div>
                    <div class='modal-footer'>
                        <button type='submit' class='btn btn-success'>Guardar Cambios</button>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>";
}
?>
</div>




<!-- Modal: Agregar Usuario -->
<div class="modal fade" id="modalAgregarUsuario" tabindex="-1" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <form id="formAgregarUsuario" action="agregar_usuario.php" method="POST">
    <div class="modal-header">
          <h5 class="modal-title" id="modalAgregarUsuarioLabel">Nuevo Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Rol:</label>
                <select name="rol" class="form-select" required>
                    <option value="Administrador">Administrador</option>
                    <option value="Usuario">Usuario</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById("formAgregarUsuario").addEventListener("submit", function(e) {
    const rol = document.querySelector("select[name='rol']").value;

    if (rol === "Administrador") {
        e.preventDefault(); // Detiene el envío

        Swal.fire({
            title: "¿Estás seguro?",
            text: "Estás creando un usuario con rol de Administrador. ¿Deseas continuar?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, crear",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                e.target.submit(); // Enviar formulario si confirma
            }
        });
    }
});
</script>



<!-- jQuery (primero, antes que todo lo demás) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Activar DataTable + Filtro -->




<script>
    $(document).ready(function () {
        $('#tablaUsuarios').DataTable({
            pageLength: 100,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });
    });
</script>


</body>
</html> 