<?php

    $url = $_SERVER['REQUEST_URI'];

    $parseUrl = parse_url($url);
    $chemin = ltrim($parseUrl['path'], '/mspr/api.php/');

    // Paramètres de connexion à la base de données
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "mspr";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("La connexion à la base de données a echoue : " . $conn->connect_error);
    }
    header('Content-Type: application/json');


    if ($chemin == "") {
        echo'success';
    } else if ($chemin == "inscription") {
        $prenom = $_GET['prenom'];
        $nom = $_GET['nom'];
        $email = $_GET['email'];
        $telephone = $_GET['telephone'];
        $mdp = $_GET['mdp'];
        $statut = $_GET['statut'];

        // Préparation de la requête d'insertion
        $query = "INSERT INTO `mspr`.`utilisateur` (`ut_prenom`, `ut_nom`, `ut_email`, `ut_telephone`, `ut_mdp`, `ut_statut`) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $prenom, $nom, $email, $telephone, $mdp, $statut);
        $stmt->execute();

        $select = "SELECT ut_id FROM utilisateur WHERE ut_nom = ? AND ut_prenom = ? AND ut_mdp = ? LIMIT 1";
        $stmt = $conn->prepare($select);
        $stmt->bind_param("sss", $nom, $prenom, $mdp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode($row);
        } else {
            echo json_encode(array('ut_id' => 'erreur'));
        }

        $stmt->close();
        $conn->close();

    } else if ($chemin == "connexion") {
        $email = $_POST['email'];
        $mdp = $_POST['mdp'];
        $query = "SELECT ut_id FROM utilisateur WHERE ut_email = '$email' AND ut_mdp = '$mdp';";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode($row);
        } else {
            echo json_encode(array('ut_id' => 'refused'));
        }
    }

    $conn->close();

