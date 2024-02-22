<?php


$url = $_SERVER['REQUEST_URI'];

$chemin = ltrim(parse_url($url, PHP_URL_PATH), '/mspr/back/api.php/');

//echo $chemin;

$database = "bdd.db";

$conn = new SQLite3($database);;

if (!$conn) {
    die("La connexion à la base de données a échoué");
}
header('Content-Type: application/json');

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

} else if ($chemin == "onnexion") {
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];

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
} else if ($chemin == "getUserInfo") {
    $id = $_GET['id'];
    
    $query = "SELECT ut_nom, ut_prenom, ut_statut, ut_email, ut_telephone FROM Utilisateur WHERE ut_id = ". $id;
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
} else if ($chemin == "reateGarde") {

    $adresse = $_GET['adresse'];
    $description = $_GET['description'];
    $proprio = $_GET['proprio'];
    
    $query = "INSERT INTO Garde (ga_adresse, ga_description, fk_utilisateur_proprietaire, fk_utilisateur_volontaire) VALUES (:adresse, :description, :proprio, '')";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':adresse', $adresse, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':proprio', $proprio, SQLITE3_TEXT);
    $stmt->execute();

} else if ($chemin == "reateImage") {
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

}  else if ($chemin == "reatePlante") {

    $garde = $_GET['garde'];
    $nom = $_GET['nom'];
    
    $query = "INSERT INTO Plante (pl_nom, fk_garde) VALUES (:nom, :garde)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':garde', $garde, SQLITE3_TEXT);
    $stmt->execute();

}