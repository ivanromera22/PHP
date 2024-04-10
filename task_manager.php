<?php

$servername = "localhost";
$username = "root";
$password = "L@p1neda";
$db = "tasks";

// Crear conexió
$conn = mysqli_connect($servername, $username, $password, $db);

// Comprovar conexió
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "";

if (!CLI()) {
    echo "Aquest script és d'unic ús a CLI\n";
    return true;
}

function CLI() {
    return php_sapi_name() === 'cli';
}

// AFEGIR TASCA
function afegir_tasca($conn, $titol, $descripcio) {
    if (empty($titol) || empty($descripcio)) {
        echo "[Error de sintaxis] Inserta text dins dels camps.\n";
        return;
    }

    $query = "INSERT INTO table_tasks (titol, descripcio) VALUES ('$titol', '$descripcio')";
    $exec = mysqli_query($conn, $query);

    if ($exec) {
        echo "Tasca afegida amb èxit.\n";
    } else {
        echo "Error al afegir la tasca.\n";
    }
}

// EDITAR TASCA
function editar_tasca($conn, $titol) {

    if (empty($titol)) {
        echo "[Error de sintaxis] Inserta text en el camp.\n";
        return;
    }

    $query = "UPDATE table_tasks SET estat = '1' WHERE titol = '$titol'";
    $exec = mysqli_query($conn, $query);
    if ($exec) {
        echo "Tasca editada amb èxit.\n";
    } else {
        echo "Error al afegir la tasca.\n";
    }
}

// LLISTAR TASCA //
function llistar_tasca($conn) {

    $query = "SELECT id, titol, descripcio FROM table_tasks WHERE estat = 0";
    $exec = mysqli_query($conn, $query);

    if ($exec) {
        echo "LListant tasques pendents:\n";
        while ($row = mysqli_fetch_assoc($exec)) {
            echo "ID: " . $row["id"] . ", Titulo: " . $row["titol"] . ", Descripción: " . $row["descripcio"] . "\n";
        }
    } else {
        echo "No hi ha tasques pendents.\n";
    }
}

// ELIMINAR TASCA /
function eliminar_tasca($conn, $titol) {

    if (empty($titol)) {
        echo "[Error de sintaxis] Inserta text en el camp.\n";
        return;
    }

    $query = "DELETE FROM table_tasks WHERE id = '$titol'";
    $exec = mysqli_query($conn, $query);

    if ($exec) {
        echo "Tasca eliminada amb èxit.\n";
    } else {
        echo "Error al eliminar la tasca.\n";
    }
}

function help() {
    echo "\n========================================================================\n";
    echo "Això es un gestor de tasques\n";
    echo "(Recorda que la sintaxis es molt important)\n";
    echo "Per afegir: php task_manager add <titol> <descripció>\n";
    echo "Per editar: php task_manager edit <titol> \n";
    echo "Per eliminar: php task_manager delete <titol>\n";
    echo "Per llistar tasques pendents: php task_manager list\n";
    echo "========================================================================\n\n";
}

switch ($argv[1]) {
    case "add":
        if ($argc == 4) {
            afegir_tasca($conn, $argv[2], $argv[3]);
        } else {
            echo "error : php task_manager.php add <titul> <descripció>";
        }
        break;
    case "edit":
        if ($argc == 3) {
            editar_tasca($conn, $argv[2]);
        } else {
            echo "error : php task_manager.php edit <titul>";
        }
        break;
    case "delete":
        if ($argc == 3) {
            eliminar_tasca($conn, $argv[2]);
        } else {
            echo "error : php task_manager.php delete <titul>";
        }
        break;
    case "list":
        if ($argc == 2) {
            llistar_tasca($conn);
        } else {
            echo "error : php task_manager.php list";
        }
        break;
    default:
        help();

}
?>
