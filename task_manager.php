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

    $query = "INSERT INTO tasques (titol, descripcio) VALUES ('$titol', '$descripcio')";
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

    $query = "UPDATE tasques SET estat = '1' WHERE titol = '$titol'";
    $exec = mysqli_query($conn, $query);
    if ($exec) {
        echo "Tasca editada amb èxit.\n";
    } else {
        echo "Error al afegir la tasca.\n";
    }
}

// LLISTAR TASCA 
function llistar_tasca($conn) {

    $query = "SELECT id, titol, descripcio, estat FROM tasques WHERE estat = 0";
    $exec = mysqli_query($conn, $query);

    $num_rows = mysqli_num_rows($exec);

    if ($num_rows > 0) {
        echo "LListant tasques pendents:\n";
        while ($row = mysqli_fetch_assoc($exec)) {
            echo "ID: " . $row["id"] . ", Títol: " . $row["titol"] . ", Descripció: " . $row["descripcio"] . ", Estat: " . $row["estat"] . "\n";
        }
    } else {
        echo "No hi ha tasques pendents.\n";
    }
}

// ELIMINAR TASCA
function eliminar_tasca($conn, $titol) {

    if (empty($titol)) {
        echo "[Error de sintaxis] Inserta text en el camp.\n";
        return;
    }

    $query = "DELETE FROM tasques WHERE id = '$titol'";
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
    echo "Per afegir: php task_manager add <títol> <descripció>\n";
    echo "Per editar: php task_manager edit <títol> \n";
    echo "Per eliminar: php task_manager delete <id>\n";
    echo "Per llistar tasques pendents: php task_manager list\n";
    echo "========================================================================\n\n";
}

switch ($argv[1]) {
    case "add":
        if ($argc == 4) {
            afegir_tasca($conn, $argv[2], $argv[3]);
        } else {
            echo "error : php task_manager.php add <titol> <descripció>\n"; 
        }
        break;
        
    case "edit":
        if ($argc == 3) {
            editar_tasca($conn, $argv[2]);
        } else {
            echo "error : php task_manager.php edit <titol>\n";
        }
        break;
        
    case "delete":
        if ($argc == 3) {
            eliminar_tasca($conn, $argv[2]);
        } else {
            echo "error : php task_manager.php delete <titol>\n";
        }
        break;
    case "list":
        if ($argc == 2) {
            llistar_tasca($conn);
        } else {
            echo "error : php task_manager.php list\n";
        }
        break;
    default:
        help();

}
?>
