<?php


$url = $_SERVER['REQUEST_URI'];

$chemin = ltrim(parse_url($url, PHP_URL_PATH));

$chemin = substr($chemin, 30);
//echo $chemin;

$database = "bdd.db";

$conn = new SQLite3($database);

if (!$conn) {
    die("La connexion à la base de données a échoué");
}
header('Content-Type: application/json');

if ($chemin == "") {
    echo 'reussie';
} else if ($chemin == "inscription") {
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $mdp = $_POST['mdp'];
    $statut = $_POST['statut'];

    inscription($prenom, $nom, $email, $telephone, $mdp, $statut, $conn);
    connexion($email, $mdp, $conn);

} else if ($chemin == "connexion") {
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];

    connexion($email, $mdp, $conn);
    
} else if ($chemin == "getUncompletedGarde") {
    gardesLibre($conn);

} else if ($chemin == "getUserInfos") {
    $id = $_POST['id'];
    
    infosUtilisateur($id, $conn);

} else if ($chemin == "getMyGarde") {
    $id = $_POST['id'];
    
    mesGardes($id, $conn);
    
} else if ($chemin == "createGarde") {
    $adresse = $_POST['adresse'];
    $description = $_POST['description'];
    $proprio = $_POST['proprio'];
    
    creerGarde($adresse, $description, $proprio, $conn);

} else if ($chemin == "createImage") {
    $path = $_POST['path'];
    $nom = $_POST['nom'];
    $utilisateur = $_POST['utilisateur'];
    $plante = $_POST['plante'];
    
    $query = "INSERT INTO Photo (ph_nom, ph_chemin, fk_utilisateur, fk_plante) VALUES (:nom, :chemin, :utilisateur, :plante)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':chemin', $path, SQLITE3_TEXT);
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':utilisateur', $utilisateur, SQLITE3_TEXT);
    $stmt->bindValue(':plante', $plante, SQLITE3_TEXT);
    $stmt->execute();

}  else if ($chemin == "createPlante") {

    $garde = $_POST['garde'];
    $nom = $_POST['nom'];
    
    $query = "INSERT INTO Plante (pl_nom, fk_garde) VALUES (:nom, :garde)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':garde', $garde, SQLITE3_TEXT);
    $stmt->execute();

}   else if ($chemin == "takeGarde") {
    $idGarde = $_POST['idGarde'];
    $idUser = $_POST['idUser'];

    prendreGarde($idGarde, $idUser, $conn);

} 

//Fonctions SQL
function connexion($email, $mdp, $conn){
    $hash_mdp = hash('sha512', $mdp);
    $query = "SELECT ut_id FROM Utilisateur WHERE ut_email = '$email' AND ut_mdp = '$hash_mdp'";
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
}

function inscription($prenom, $nom, $email, $telephone, $mdp, $statut, $conn){
    $hash_mdp = hash('sha512', $mdp);
    $query = "INSERT INTO Utilisateur (ut_prenom, ut_nom, ut_email, ut_telephone, ut_mdp, ut_statut) VALUES (:prenom, :nom, :email, :telephone, :mdp, :statut)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':prenom', $prenom, SQLITE3_TEXT);
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':telephone', $telephone, SQLITE3_TEXT);
    $stmt->bindValue(':mdp', $hash_mdp, SQLITE3_TEXT);
    $stmt->bindValue(':statut', $statut, SQLITE3_TEXT);
    $stmt->execute();
}

function gardesLibre($conn){
    $query = "SELECT ga_id, ga_adresse, ga_description, ut_prenom
    FROM Garde
    JOIN Utilisateur ON fk_utilisateur_proprietaire = ut_id
    WHERE fk_utilisateur_volontaire = '';";
    $result = $conn->query($query);

    if ($result) {
        $rows = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo json_encode(array('ga_id' => 'error'));
    }
}

function infosUtilisateur($id, $conn){
    $query = "SELECT ut_nom, ut_prenom, ut_statut, ut_email, ut_telephone FROM Utilisateur WHERE ut_id = ". $id;
    $result = $conn->query($query);

    if ($result) {
        $rows = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo json_encode(array('ut_nom' => 'error'));
    }
}

function mesGardes($id, $conn){
    $query = "SELECT ga_id, ga_adresse, ga_description, fk_utilisateur_proprietaire AS proprio, fk_utilisateur_volontaire AS volontaire FROM Garde WHERE volontaire = ". $id ." OR proprio = ". $id;
    $result = $conn->query($query);

    if ($result) {
        $rows = array();
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        echo json_encode($rows);
    } else {
        echo json_encode(array('ga_id' => 'error'));
    }
}

function creerGarde($adresse, $description, $proprio, $conn) {
    $query = "INSERT INTO Garde (ga_adresse, ga_description, fk_utilisateur_proprietaire, fk_utilisateur_volontaire) VALUES (:adresse, :description, :proprio, '')";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':adresse', $adresse, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':proprio', $proprio, SQLITE3_TEXT);
    $stmt->execute();

    $query = "SELECT ga_id FROM Garde WHERE ga_adresse = '$adresse' AND ga_description = '$description' AND fk_utilisateur_proprietaire = '$proprio' AND fk_utilisateur_volontaire = '' ORDER BY ga_id DESC";
    $result = $conn->query($query);

    if ($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row) {
            echo json_encode($row);
        } else {
            echo json_encode(array('ga_id' => 'refused'));
        }
    } else {
        echo json_encode(array('ga_id' => 'refused'));
    }
}

function prendreGarde($idGarde, $idUser, $conn){
    try {
        $query = "UPDATE Garde SET fk_utilisateur_volontaire = :idUser WHERE ga_id = :idGarde;";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':idUser', $idUser, SQLITE3_TEXT);
        $stmt->bindValue(':idGarde', $idGarde, SQLITE3_TEXT);
        $success = $stmt->execute();

        if ($success) {
            echo "success";
        } else {
            echo "fail";
        }
    } catch (Exception $e) {
        echo "fail : " . $e->getMessage();
    }
}
