<?php

    // $url = $_SERVER['REQUEST_URI'];

    // $parseUrl = parse_url($url);
    // $chemin = ltrim($parseUrl['path'], '/mspr/api.php/');

    // // Paramètres de connexion à la base de données
    // $servername = "localhost";
    // $username = "root";
    // $password = "";
    // $database = "mspr";

    // $conn = new mysqli($servername, $username, $password, $database);

    // if ($conn->connect_error) {
    //     die("La connexion à la base de données a echoue : " . $conn->connect_error);
    // }
    // header('Content-Type: application/json');


    // if ($chemin == "") {
    //     echo'success';
    // } else if ($chemin == "inscription") {
    //     $prenom = $_GET['prenom'];
    //     $nom = $_GET['nom'];
    //     $email = $_GET['email'];
    //     $telephone = $_GET['telephone'];
    //     $mdp = $_GET['mdp'];
    //     $statut = $_GET['statut'];

    //     // Préparation de la requête d'insertion
    //     $query = "INSERT INTO `mspr`.`utilisateur` (`ut_prenom`, `ut_nom`, `ut_email`, `ut_telephone`, `ut_mdp`, `ut_statut`) VALUES (?, ?, ?, ?, ?, ?)";
    //     $stmt = $conn->prepare($query);
    //     $stmt->bind_param("ssssss", $prenom, $nom, $email, $telephone, $mdp, $statut);
    //     $stmt->execute();

    //     $select = "SELECT ut_id FROM utilisateur WHERE ut_nom = ? AND ut_prenom = ? AND ut_mdp = ? LIMIT 1";
    //     $stmt = $conn->prepare($select);
    //     $stmt->bind_param("sss", $nom, $prenom, $mdp);
    //     $stmt->execute();
    //     $result = $stmt->get_result();

    //     if ($result->num_rows > 0) {
    //         $row = $result->fetch_assoc();
    //         echo json_encode($row);
    //     } else {
    //         echo json_encode(array('ut_id' => 'erreur'));
    //     }

    //     $stmt->close();
    //     $conn->close();

    // } else if ($chemin == "connexion") {
    //     $email = $_POST['email'];
    //     $mdp = $_POST['mdp'];
    //     $query = "SELECT ut_id FROM utilisateur WHERE ut_email = '$email' AND ut_mdp = '$mdp';";
    //     $result = $conn->query($query);

    //     if ($result->num_rows > 0) {
    //         $row = $result->fetch_assoc();
    //         echo json_encode($row);
    //     } else {
    //         echo json_encode(array('ut_id' => 'refused'));
    //     }
    // }

    // $conn->close();

?>

<?php
$url = $_SERVER['REQUEST_URI'];

$parseUrl = parse_url($url);
$chemin = ltrim($parseUrl['path'], './mspr/api.php/');



// Chemin de la base de données SQLite
$database = 'C:\xampp\htdocs\bdd.db';

// Chemin de la base de données SQLite en produciton
//$database = '/var/www/html/bdd.db';

// Connexion à la base de données SQLite'
$conn = new SQLite3($database);

if (!$conn) {
    die("La connexion à la base de données a échoué");
}
//header('Content-Type: application/json');

if ($chemin == "") {
    echo 'success';
} else if ($chemin == "inscription") {
    $prenom = $_GET['prenom'];
    $nom = $_GET['nom'];
    $email = $_GET['email'];
    $telephone = $_GET['telephone'];
    $mdp = $_GET['mdp'];
    $statut = $_GET['statut'];

    // Préparation de la requête d'insertion
    $query = "INSERT INTO Utilisateur (ut_prenom, ut_nom, ut_email, ut_telephone, ut_mdp, ut_statut) VALUES (:prenom, :nom, :email, :telephone, :mdp, :statut)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':prenom', $prenom, SQLITE3_TEXT);
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':telephone', $telephone, SQLITE3_TEXT);
    $stmt->bindValue(':mdp', $mdp, SQLITE3_TEXT);
    $stmt->bindValue(':statut', $statut, SQLITE3_TEXT);
    $stmt->execute();

    // Récupération de l'ID de l'utilisateur inséré
    $select = "SELECT ut_id FROM Utilisateur WHERE ut_nom = :nom AND ut_prenom = :prenom AND ut_mdp = :mdp LIMIT 1";
    $stmt = $conn->prepare($select);
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':prenom', $prenom, SQLITE3_TEXT);
    $stmt->bindValue(':mdp', $mdp, SQLITE3_TEXT);
    $result = $stmt->execute();

    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        echo json_encode($row);
    } else {
        echo json_encode(array('ut_id' => 'refused'));
    }

} else if ($chemin == "connexion") {
    $email = isset($_GET['email']) ? $_GET['email'] : '';
    $mdp = isset($_GET['mdp']) ? $_GET['mdp'] : '';

    $query = "SELECT ut_id FROM Utilisateur WHERE ut_email = '$email' AND ut_mdp = '$mdp'";
    //$query = "SELECT name FROM sqlite_master WHERE type='table'";
    $result = $conn->query($query);

    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row) {
            echo json_encode($row);
        } else {
            echo json_encode(array('ut_id' => 'refused'));
        }
    } else {
        echo json_encode(array('ut_id' => 'refused'));
    }
} else if ($chemin == "getUncompletedGarde") {
    
    $query = "SELECT ga_id, ga_adresse, ga_description, fk_utilisateur_proprietaire AS proprio, fk_utilisateur_volontaire AS volontaire FROM Garde WHERE volontaire = ''";
    $result = $conn->query($query);

    if ($result) {
        $rows = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo json_encode(array('return' => 'error'));
    }
} else if ($chemin == "getMyGarde") {

    $id = $_GET['id'];
    
    $query = "SELECT ga_id, ga_adresse, ga_description, fk_utilisateur_proprietaire AS proprio, fk_utilisateur_volontaire AS volontaire FROM Garde WHERE volontaire = ". $id ." OR proprio = ". $id;
    $result = $conn->query($query);

    if ($result) {
        $rows = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo json_encode(array('return' => 'error'));
    }
} else if ($chemin == "createGarde") {

    $adresse = $_GET['adresse'];
    $description = $_GET['description'];
    $proprio = $_GET['proprio'];
    
    $query = "INSERT INTO Garde (ga_adresse, ga_description, fk_utilisateur_proprietaire, fk_utilisateur_volontaire) VALUES (:adresse, :description, :proprio, '')";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':adresse', $adresse, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':proprio', $proprio, SQLITE3_TEXT);
    $stmt->execute();

} else if ($chemin == "createImage") {
    $path = $_GET['path'];
    $nom = $_GET['nom'];
    $utilisateur = $_GET['utilisateur'];
    $plante = $_GET['plante'];
    
    $query = "INSERT INTO Photo (ph_nom, ph_chemin, fk_utilisateur, fk_plante) VALUES (:nom, :chemin, :utilisateur, :plante)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':chemin', $path, SQLITE3_TEXT);
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':utilisateur', $utilisateur, SQLITE3_TEXT);
    $stmt->bindValue(':plante', $plante, SQLITE3_TEXT);
    $stmt->execute();

}  else if ($chemin == "createPlante") {

    $garde = $_GET['garde'];
    $nom = $_GET['nom'];
    
    $query = "INSERT INTO Plante (pl_nom, fk_garde) VALUES (:nom, :garde)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':garde', $garde, SQLITE3_TEXT);
    $stmt->execute();

} 

$conn->close();

?>