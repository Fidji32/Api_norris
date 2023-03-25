<?php
session_start();
echo '<form action="index.php"><button type="submit" name="last10">Deconnexion</button></form>';

function thead()
{
    echo '<table>
        <thead>
            <th>Id</th>
            <th>Phrase</th>
            <th>Vote</th>
            <th>Date d\'ajout</th>
            <th>Date de modification</th>
            <th>Fautes</th>
            <th>Signalements</th>
            <th>Modification</th>
            <th>Suppression</th>
        </thead><tbody>';
}

function retourDonnées($data, $vote, $signalement)
{
    thead();
    foreach ($data['data'] as $v) {
        echo '<tr>';
        echo '<td>' . $v['id'] . '</td>';
        echo '<td>' . $v['phrase'] . '</td>';
        if ($vote == 1) {
            echo '<td><button value="' . $v['id'] . '" type="submit" name="moins">-</button>' . $v['vote'] . '<button value="' . $v['id'] . '" type="submit" name="plus">+</button></td>';
        } else {
            echo '<td>' . $v['vote'] . '</td>';
        }
        echo '<td>' . $v['date_ajout'] . '</td>';
        echo '<td>' . $v['date_modif'] . '</td>';
        echo '<td>' . $v['faute'] . '</td>';
        if ($signalement == 1) {
            echo '<td><button value="' . $v['id'] . '" type="submit" name="moinsS">-</button>' . $v['signalement'] . '<button value="' . $v['id'] . '" type="submit" name="plusS">+</button></td>';
        } else {
            echo '<td>' . $v['signalement'] . '</td>';
        }
        echo '<td><button type="submit" value="' . $v['id'] . '" name="modifier">Modifier</button></td><td><button type="submit" value="' . $v['id'] . '" name="supprimer">Supprimer</button></td></tr>';
    }
    echo '</tbody></table>';
}

function methodeNoBody($post, $methode)
{
    if (isset($_POST[$post])) {
        // Cas des méthodes GETALL
        $result = file_get_contents(
            'http://localhost/Rest/serveur.php?id=' . $post,
            false,
            stream_context_create(array('http' => array(
                'method' => $methode,
                'header' => 'Authorization:' . $_SESSION['jwt']
            ))) // ou DELETE
        );
        $data = json_decode($result, true);
        echo '<form action="client.php" method="POST">';
        retourDonnées($data, 0, 0);
        echo '</form>';
    }
}

function methodeBody($post, $postArray)
{
    if (isset($_POST[$post])) {
        $data = array("phrase" => $_POST[$postArray]);
        $data_string = json_encode($data);
        /// Envoi de la requête
        if ($post == "add") {
            $result = file_get_contents(
                'http://localhost/Rest/serveur.php',
                false,
                stream_context_create(array(
                    'http' => array(
                        'method' => 'POST', // ou PUT
                        'content' => $data_string,
                        'header' => array('Content-Type: application/json' . "\r\n"
                            . 'Content-Length: ' . strlen($data_string) . "\r\n"
                            . 'Authorization:' . $_SESSION['jwt'] . "\r\n")
                    )
                ))
            );
        } else {
            $result = file_get_contents(
                'http://localhost/Rest/serveur.php?id=' . $_POST[$post],
                false,
                stream_context_create(array(
                    'http' => array(
                        'method' => 'PUT', // ou PUT
                        'content' => $data_string,
                        'header' => array('Content-Type: application/json' . "\r\n"
                            . 'Content-Length: ' . strlen($data_string) . "\r\n"
                            . 'Authorization:' . $_SESSION['jwt'] . "\r\n")
                    )
                ))
            );
        }
        $data = json_decode($result, true);
        echo '<h2>Dernière modification :</h2>';
        echo '<form action="client.php" method="POST">';
        retourDonnées($data, 0, 0);
        echo '</form>';
    }
}

function methodePatch($post, $action, $name)
{
    if (isset($_POST[$post])) {
        $data = array("action" => $action, "name" => $name);
        $data_string = json_encode($data);
        /// Envoi de la requête
        $result = file_get_contents(
            'http://localhost/Rest/serveur.php?id=' . $_POST[$post],
            false,
            stream_context_create(array(
                'http' => array(
                    'method' => 'PATCH',
                    'content' => $data_string,
                    'header' => array('Content-Type: application/json' . "\r\n"
                        . 'Content-Length: ' . strlen($data_string) . "\r\n"
                        . 'Authorization:' . $_SESSION['jwt'] . "\r\n")
                )
            ))
        );
        $data = json_decode($result, true);
        echo '<h2>Dernière modification :</h2>';
        echo '<form action="client.php" method="POST">';
        retourDonnées($data, 1, 1);
        echo '</form>';
    }
}

function delete()
{
    if (isset($_POST['supprimer'])) {
        $result = file_get_contents(
            'http://localhost/Rest/serveur.php?id=' . $_POST['supprimer'],
            false,
            stream_context_create(array('http' => array(
                'method' => 'DELETE',
                'header' => 'Authorization:' . $_SESSION['jwt']
            )))
        );
    }
}

//! last10
methodeNoBody("last10", "GET");

//! signalement
methodeNoBody("signalement", "GET");

//! vote
methodeNoBody("vote", "GET");

//! delete
delete();

//! put
methodeBody("validation", "modif");

//! Plus 1 vote
methodePatch("plus", "plus", "vote");

//! Moins 1 vote
methodePatch("moins", "moins", "vote");

//! Plus 1 signalement
methodePatch("plusS", "plus", "signalement");

//! Moins 1 signalement
methodePatch("moinsS", "moins", "signalement");

//! Post
methodeBody("add", "phrase");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <form action="client.php" method="POST">
        <h2>Nouvelles fonctionnalités :</h2>
        <div>
            <div class="fils">
                <label for="signalement">Last 10 : </label><button type="submit" name="last10">Valider</button>
            </div>
            <div class="fils">
                <label for="signalement">Vote : </label><button type="submit" name="vote">Valider</button>
            </div>
            <div class="fils">
                <label for="signalement">Signalement : </label><button type="submit" name="signalement">Valider</button>
            </div>
        </div>
        <h2>Ajouter une phrase :</h2>
        <input type="text" name="phrase">
        <button type="submit" name="add">Add</button>
        <?php
        // GETID
        if (isset($_POST['modifier'])) {
            $result = file_get_contents(
                'http://localhost/Rest/serveur.php?id=' . $_POST['modifier'],
                false,
                stream_context_create(array('http' => array(
                    'method' => 'GET',
                    'header' => 'Authorization:' . $_SESSION['jwt']
                ))) // ou DELETE
            );
            $data = json_decode($result, true);
            foreach ($data['data'] as $v) {
                echo '<h2>Modifier votre phrase :</h2><tr>';
                echo '<td><input type="text" name="modif" value="' . $v['phrase'] . '"></td>';
                echo '<td><button type="submit" value="' . $v['id'] . '" name="validation">Valider</button></td>';
            }
        }
        ?>
        <?php
        // Cas des méthodes GETALL
        $result = file_get_contents(
            'http://localhost/Rest/serveur.php',
            false,
            stream_context_create(array('http' => array(
                'method' => 'GET',
                'header' => 'Authorization:' . $_SESSION['jwt']
            ))) // ou DELETE
        );
        $data = json_decode($result, true);
        retourDonnées($data, 1, 1);
        ?>
    </form>
</body>

</html>