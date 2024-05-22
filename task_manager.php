<?php
$servername = "localhost";
$username = "root";
$password = "L@p1neda";
$db = "tasks";

$fitxerConf = getenv('HOME') . '/.config/task_manager.cfg';
$jsonFile = getenv('HOME') . '/.config/task-manager.json';


function carregar_missatges($arxiu) {
    if (file_exists($arxiu)) {
        $json = file_get_contents($arxiu);
        return json_decode($json, true);
    } else { 
        die("No s'ha trobat el fitxer de missatges. \n");
    }
}
$missatges = carregar_missatges('messages.json');


$config = parse_ini_file($fitxerConf, true);
$tipusGuardar = $config['Main']['storage-type'] ?? '';

if (empty($tipusGuardar)) {
    echo $missatges["seleccionar_tipus"];
    $tipusGuardar = trim(fgets(STDIN));
    
    if ($tipusGuardar != 'sql' && $tipusGuardar != 'json') {
        die($missatges["tipus_invalid"]);
    }
    
    file_put_contents($fitxerConf, "[Main]\nstorage-type = $tipusGuardar\n");
}

if (substr(php_sapi_name(), 0, 3) != 'cli') {
    echo $missatges["cli_only"];
}


// SQL
if ($tipusGuardar == 'sql') {
    $conn = mysqli_connect($servername,$username, $password);

    if (!$conn) {
        die($missatges["connexio_fallida_sql"]);
    }

    $sql = "CREATE DATABASE IF NOT EXISTS $db";
    if (!mysqli_query($conn, $sql)) {
        die($missatges["error_crear_bd"]);
    } 
    
    mysqli_select_db($conn, $db);

    $sql = "CREATE TABLE IF NOT EXISTS tasques (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titol VARCHAR(255) NOT NULL,
        descripcio TEXT,
        estat BOOLEAN NOT NULL DEFAULT 0
    );";

    if (!mysqli_query($conn, $sql)) {
        die($missatges["error_crear_taula"]);
    } 
    mysqli_close($conn);
}

//json
if ($tipusGuardar == 'json') {
    if (!file_exists($jsonFile)) {
        $startFitxer = [];
        if (file_put_contents($jsonFile, json_encode($startFitxer))) {
        } else {
            die($missatges["error_crear_arxiu_json"]);
        }
    }
}

$options = getopt("a:d:t:", ["action:", "description:", "title:"]);



function carregar_tasquesJSON($jsonFile){
    global $missatges;
    if (file_exists($jsonFile)) {
        $json = file_get_contents($jsonFile);
        return json_decode($json, true);
    } else {
        die($missatges["error_tasques_json"]);
    }
}


function guardarTasquesJSON($jsonFile, $tasques) {
    file_put_contents($jsonFile, json_encode($tasques));
}


// AFEGIR TASCA 
function afegir_tasca($tipusGuardar, $titol, $descripcio) {
    global  $missatges, $servername, $username, $password, $db, $jsonFile;

    if (empty($titol) || empty($descripcio)) {
        echo $missatges["tasca_camps8"];
        return;
    }
    
    // AFEGIR SQL
    if ($tipusGuardar == 'sql') {
        $conn = mysqli_connect($servername, $username, $password, $db);
        if (!$conn) {
            die($missatges["connexio_fallida_sql"]);
        }

        $query = "INSERT INTO tasques (titol, descripcio) VALUES ('$titol', '$descripcio')";
        $exec = mysqli_query($conn, $query);

        if ($exec) {
            echo $missatges["tasca_afegida"];
        } else {
            echo $missatges["tasca_error"];
        }
        mysqli_close($conn);
    }

    // AFEGIR JSON
    elseif ($tipusGuardar == 'json') {
        $tasques = carregar_tasquesJSON($jsonFile);
        $tasques[] = ['id' => count($tasques) + 1, 'titol' => $titol, 'descripcio' => $descripcio, 'estat' => 0];
        guardarTasquesJSON($jsonFile, $tasques);
        echo $missatges["tasca_afegida"];
    }
    
}

// EDITAR TASCA
function editar_tasca($tipusGuardar, $titol) {
    global $missatges, $servername, $username, $password, $db, $jsonFile;
    
    if (empty($titol)) {
        echo $missatges["tasca_camps8"];
        return;
    }

    // EDITAR SQL
    if ($tipusGuardar == 'sql') {
        $conn = mysqli_connect($servername, $username, $password, $db);
        if (!$conn) {
            die($missatges["connexio_fallida_sql"]);
        }
        $query = "SELECT * FROM tasques WHERE id = '$titol'";
        $exec = mysqli_query($conn, $query);

         if (mysqli_num_rows($exec) > 0) {
            $row = mysqli_fetch_assoc($exec);
            if ($row['estat'] == 1) {
                echo $missatges["tasca_ja_editada"];
            } else {
                $query = "UPDATE tasques SET estat = '1' WHERE id = '$titol'";
                $exec = mysqli_query($conn, $query);
                echo $missatges["tasca_edit"];
            }
        } else {
            echo $missatges["tasca_noTrobada"];
        }
        mysqli_close($conn);
    }

    // EDITAR JSON
    elseif ($tipusGuardar == 'json') {
        $tasques = carregar_tasquesJSON($jsonFile);
        foreach ($tasques as &$tasca) {
            if ($tasca['id'] == $titol) {
                if ($tasca['estat'] == 1) {
                    echo $missatges["tasca_ja_editada"];
                } else {
                    $tasca['estat'] = 1;
                    echo $missatges["tasca_edit"];
                    guardarTasquesJSON($jsonFile, $tasques);
                }
                return;
            }
        }
        echo $missatges["tasca_noTrobada"];
    }
}

// LLISTAR TASCA 
function llistar_tasca($tipusGuardar) {
    global $missatges, $servername, $username, $password, $db, $jsonFile;

    // LLISTAR SQL
    if ($tipusGuardar == 'sql') {
        $conn = mysqli_connect($servername, $username, $password, $db);
        if (!$conn) {
            die($missatges["connexio_fallida_sql"]);
        }
        
        $query = "SELECT id, titol, descripcio, estat FROM tasques WHERE estat = 0";
        $exec = mysqli_query($conn, $query);

        $num_rows = mysqli_num_rows($exec);

        if ($num_rows > 0) {
            echo $missatges["llistar_tasques"];
            while ($row = mysqli_fetch_assoc($exec)) {
                echo "ID: " . $row["id"] . ", Títol: " . $row["titol"] . ", Descripció: " . $row["descripcio"] . ", Estat: " . $row["estat"] . "\n";
            }
        } else {
            echo $missatges["noTasques_pendents"];
        }
        mysqli_close($conn);
    }

    // LLISTAR JSON
    elseif ($tipusGuardar == 'json') {
        $tasques = carregar_tasquesJSON($jsonFile);
        $tasques_pendents = [];
        foreach ($tasques as $tasca) {
            if ($tasca['estat'] == 0) {
                $tasques_pendents[] = $tasca;
            }
        }

        if (count($tasques_pendents) > 0) {
            echo $missatges["llistar_tasques"];
            foreach ($tasques_pendents as $tasca) {
                echo "ID: " . $tasca["id"] . ", Títol: " . $tasca["titol"] . ", Descripció: " . $tasca["descripcio"] . ", Estat: " . $tasca["estat"] . "\n";
            }
        } else {
            echo $missatges["noTasques_pendents"];
        }
    }
}

// ELIMINAR TASCA
function eliminar_tasca($tipusGuardar, $titol) {
    global $missatges, $servername, $username, $password, $db, $jsonFile;

    if (empty($titol)) {
        echo $missatges["tasca_camps8"];
        return;
    }

    //ELIMINAR SQL
    if ($tipusGuardar == 'sql') {
        $conn = mysqli_connect($servername, $username, $password, $db);
        if (!$conn) {
            die($missatges["connexio_fallida_sql"]);
        }

        $query = "SELECT * FROM tasques WHERE id = '$titol'";
        $exec = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($exec) > 0) {
            $query = "DELETE FROM tasques WHERE id = '$titol'";
            $exec = mysqli_query($conn, $query);
            echo ["tasca_eliminada"];
        } else {
            echo $missatges["tasca_noTrobada"];
        }
        mysqli_close($conn);
    }
   
    // ELIMINAR JSON
    if ($tipusGuardar == 'json') {
        $tasques = carregar_tasquesJSON($jsonFile);
        foreach ($tasques as $index => $tasca) {
            if ($tasca['id'] == $titol) {
                array_splice($tasques, $index, 1);
                echo $missatges["tasca_eliminada"];
                guardarTasquesJSON($jsonFile, $tasques);
                return;
            }
        }
        echo $missatges["tasca_noTrobada"];
    }

}


switch ($options["a"] ?? $options["action"] ?? null) {
    case "add":
        if ($argc == 7) {
            $titol = $options["t"] ?? $options["title"];
            $descripcio = $options["d"] ?? $options["description"];

            afegir_tasca($tipusGuardar, $titol, $descripcio);
        } else {
            echo $missatges["add_help"];
        }
        break;
        
    case "edit":
        if ($argc == 5) {
            $titol = $options["t"] ?? $options["title"];
            editar_tasca($tipusGuardar, $titol);
        } else {
            echo $missatges["edit_help"];
        }
        break;
        
    case "delete":
        if ($argc == 5) {
            $titol = $options["t"] ?? $options["title"];
            eliminar_tasca($tipusGuardar, $titol);
        } else {
            echo $missatges["delete_help"];
        }
        break;
    case "list":
        if ($argc == 3) {
            llistar_tasca($tipusGuardar);
        } else {
            echo $missatges["list_help"];
        }
        break;
    default:
        echo $missatges["help"];
        
}
?>