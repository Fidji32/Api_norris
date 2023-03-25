<?php
session_start();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Page d'authentification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        .login-container {
            background-color: #fff;
            max-width: 400px;
            margin: auto;
            margin-top: 100px;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            box-sizing: border-box;
            border: 2px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<?php

function methodeBody($postArray, $postArray2)
{
    $data = array("login" => $_POST[$postArray], "mdp" => $_POST[$postArray2]);
    $data_string = json_encode($data);
    /// Envoi de la requête
    $result = file_get_contents(
        'http://localhost/Rest/authentification.php',
        false,
        stream_context_create(array(
            'http' => array(
                'method' => 'POST', // ou PUT
                'content' => $data_string,
                'header' => array('Content-Type: application/json' . "\r\n"
                    . 'Content-Length: ' . strlen($data_string) . "\r\n")
            )
        ))
    );
    $data = json_decode($result, true);
    if ($data['data'] == "error") {
        echo '<p class="error-message">Login ou mot de passe invalide !</p>';
    } else {
        $_SESSION['jwt'] = $data['data'];
        header('location: client.php');
    }
}

?>

<body>
    <div class="login-container">
        <h1>Connexion</h1>
        <form method="post">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Se connecter</button>

            <div class="error-message">
                <?php
                if (isset($_POST['username']) && isset($_POST['password'])) {
                    // vérifier les identifiants et afficher un message d'erreur si nécessaire
                    methodeBody('username', 'password');
                }
                ?>
            </div>
        </form>
    </div>
</body>

</html>