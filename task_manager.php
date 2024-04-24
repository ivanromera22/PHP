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

$options = getopt("a:d:t:", ["action:", "description:", "title:"]);


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

    $query = "SELECT * FROM tasques WHERE id = '$titol'";
    $exec = mysqli_query($conn, $query);

    if (mysqli_num_rows($exec) > 0) {
        $query = "UPDATE tasques SET estat = '1' WHERE id = '$titol'";
        $exec = mysqli_query($conn, $query);
        echo "Tasca $titol editada amb èxit.\n";
    } else {
        echo "No s'ha trobat cap tasca amb l'id $titol.\n";
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

    $query = "SELECT * FROM tasques WHERE id = '$titol'";
    $exec = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($exec) > 0) {
        $query = "DELETE FROM tasques WHERE id = '$titol'";
        $exec = mysqli_query($conn, $query);
        echo "Tasca $titol eliminada amb èxit.\n";
    } else {
        echo "No s'ha trobat cap tasca amb l'id $titol.\n";
    }

}

function help() {
    echo "\n==============================================================================\n";
    echo "Això es un gestor de tasques\n";
    echo "(Recorda que la sintaxis es molt important)\n\n";

    echo "Per afegir:\n";
    echo "php task_manager -a add -t <títol> -d <descripció>\n";
    echo "php task_manager --action add --title <títol> --description <descripció>\n";
    echo "Fins i tot pots barrejar els parametres.\n";

    echo "Per editar:\n"; 
    echo "php task_manager -a edit -t <id>\n";
    echo "php task_manager --action edit --title <id> \n";
    echo "Fins i tot pots barrejar els parametres.\n";
    
    
    echo "Per eliminar:\n";
    echo "php task_manager -a delete -t <id>\n";
    echo "php task_manager --action delete --title <id> \n";
    echo "Fins i tot pots barrejar els parametres.\n";

    echo "Per llistar tasques:\n";
    echo "php task_manager -a list\n";
    echo "php task_manager --action list\n";
    echo "Fins i tot pots barrejar els parametres.\n";
    echo "==============================================================================\n\n";
}

switch ($options["a"] ?? $options["action"]) {
    case "add":
        if ($argc == 7) {
            $titol = $options["t"] ?? $options["title"];
            $descripcio = $options["d"] ?? $options["description"];

            afegir_tasca($conn, $titol, $descripcio);
        } else {
            echo "Per afegir una tasca hi ha dos formes:\n";
            echo "php task_manager -a add -t <títol> -d <descripció>\n";
            echo "php task_manager --action add --title <títol> --description <descripció>\n";
            echo "Fins i tot pots barrejar els parametres.\n";
        }
        break;
        
    case "edit":
        if ($argc == 5) {
            $titol = $options["t"] ?? $options["title"];
            editar_tasca($conn, $titol);
        } else {
            echo "Per editar una tasca hi ha dos formes\n";
            echo "php task_manager -a edit -t <id>\n";
            echo "php task_manager --action edit --title <id> \n";
            echo "Fins i tot pots barrejar els parametres.\n";
        }
        break;
        
    case "delete":
        if ($argc == 5) {
            $titol = $options["t"] ?? $options["title"];
            eliminar_tasca($conn, $titol);
        } else {
            echo "Per eliminar una tasca hi ha dos formes\n";
            echo "php task_manager -a delete -t <id>\n";
            echo "php task_manager --action delete --title <id> \n";
            echo "Fins i tot pots barrejar els parametres.\n";
        }
        break;
    case "list":
        if ($argc == 3) {
            llistar_tasca($conn);
        } else {
            echo "Fins i tot pots barrejar els parametres.\n";
            echo "php task_manager -a list\n";
            echo "php task_manager --action list\n";
        }
        break;
    default:
        help();
        
}
?>
