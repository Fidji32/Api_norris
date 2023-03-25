<?php
/// Librairies éventuelles (pour la connexion à la BDD, etc.)
include('mylib.php');

/// Paramétrage de l'entête HTTP (pour la réponse au Client)
header("Content-Type:application/json");

/// Identification du type de méthode HTTP envoyée par le client
$http_method = $_SERVER['REQUEST_METHOD'];
switch ($http_method) {

        //? Cas de la méthode GET
    case "GET":
        try {
            //! Traitement
            if (if_jwt_correct()) {
                if (isset($_GET['id'])) {
                    //! Exception
                    if (empty($_GET['id'])) {
                        throw new Exception("Missing id", 404);
                    }
                    if ($_GET['id'] == "signalement") {
                        $matchingData = getBySignalement();
                    } elseif ($_GET['id'] == "vote") {
                        $matchingData = getByVote();
                    } elseif ($_GET['id'] == "last10") {
                        $matchingData = getByLast10();
                    } else {
                        $matchingData = getId($_GET['id']);
                    }
                } else {
                    $matchingData = getALL();
                }
                $RETURN_CODE = 200;
                $STATUS_MESSAGE = "modification réussi";
            } else {
                $RETURN_CODE = 498;
                $STATUS_MESSAGE = "Tokken invalide !";
            }
        } catch (\Throwable $th) {
            $RETURN_CODE = $th->getCode();
            $STATUS_MESSAGE = $th->getMessage();
        } finally {
            //! Envoi de la réponse au Client
            deliver_response($RETURN_CODE, $STATUS_MESSAGE, $matchingData);
        }
        break;


        //! Cas de la méthode POST
    case "POST":
        try {
            if (if_jwt_correct()) {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                $postedData = json_decode($postedData, true);
                /// Traitement
                $matchingData = post($postedData['phrase']);
                $RETURN_CODE = 200;
                $STATUS_MESSAGE = "modification réussi";
            } else {
                $RETURN_CODE = 498;
                $STATUS_MESSAGE = "Tokken invalide !";
            }
        } catch (\Throwable $th) {
            $RETURN_CODE = $th->getCode();
            $STATUS_MESSAGE = $th->getMessage();
        } finally {
            //! Envoi de la réponse au Client
            deliver_response($RETURN_CODE, $STATUS_MESSAGE, $matchingData);
        }
        break;

        //! Cas de la méthode PUT
        //TODO: PATCH au lieu de PUT car pas toute la ressource
    case "PUT":
        try {
            //! Exception
            if (empty($_GET['id'])) {
                throw new Exception("Missing id", 404);
            }
            if (if_jwt_correct()) {
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                $postedData = json_decode($postedData, true);
                if (empty($postedData['phrase'])) {
                    throw new Exception("Missing phrase", 400);
                }
                /// Traitement
                if (isset($_GET['id'])) {
                    $matchingData = put($_GET['id'], $postedData['phrase']);
                }
                $RETURN_CODE = 200;
                $STATUS_MESSAGE = "modification réussi";
            } else {
                $RETURN_CODE = 498;
                $STATUS_MESSAGE = "Tokken invalide !";
            }
        } catch (\Throwable $th) {
            $RETURN_CODE = $th->getCode();
            $STATUS_MESSAGE = $th->getMessage();
        } finally {
            //! Envoi de la réponse au Client
            deliver_response($RETURN_CODE, $STATUS_MESSAGE, $matchingData);
        }
        break;
    case "PATCH":
        try {
            //! Exception
            if (empty($_GET['id'])) {
                throw new Exception("Missing id", 404);
            }
            if (if_jwt_correct()) { /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                $postedData = json_decode($postedData, true);
                if (empty($postedData['action'])) {
                    throw new Exception("Missing action", 400);
                }
                if (empty($postedData['name'])) {
                    throw new Exception("Missing name", 400);
                }
                /// Traitement
                if (isset($_GET['id'])) {
                    if ($postedData['action'] == "plus") {
                        if ($postedData['name'] == "vote") {
                            $matchingData = putVotePlus1($_GET['id']);
                        } else {
                            $matchingData = putSignalementPlus1($_GET['id']);
                        }
                    } elseif ($postedData['action'] == "moins") {
                        if ($postedData['name'] == "vote") {
                            $matchingData = putVoteMoins1($_GET['id']);
                        } else {
                            $matchingData = putSignalementMoins1($_GET['id']);
                        }
                    }
                }
                $RETURN_CODE = 200;
                $STATUS_MESSAGE = "modification réussi";
            } else {
                $RETURN_CODE = 498;
                $STATUS_MESSAGE = "Tokken invalide !";
            }
        } catch (\Throwable $th) {
            $RETURN_CODE = $th->getCode();
            $STATUS_MESSAGE = $th->getMessage();
        } finally {
            //! Envoi de la réponse au Client
            deliver_response($RETURN_CODE, $STATUS_MESSAGE, $matchingData);
        }
        break;

        //! Cas de la méthode DELETE
    case "DELETE":
        try {
            //! Exception
            if (empty($_GET['id'])) {
                throw new Exception("Missing id", 404);
            }
            if (if_jwt_correct()) {
                /// Récupération de l'identifiant de la ressource envoyé par le Client
                if (isset($_GET['id'])) {
                    /// Traitement
                    $matchingData = delete($_GET['id']);
                }
                $RETURN_CODE = 200;
                $STATUS_MESSAGE = "modification réussi";
            } else {
                $RETURN_CODE = 498;
                $STATUS_MESSAGE = "Tokken invalide !";
            }
        } catch (\Throwable $th) {
            $RETURN_CODE = $th->getCode();
            $STATUS_MESSAGE = $th->getMessage();
        } finally {
            //! Envoi de la réponse au Client
            deliver_response($RETURN_CODE, $STATUS_MESSAGE, $matchingData);
        }
        break;
    default:
        $matchingData = null;
        $RETURN_CODE = 405;
        $STATUS_MESSAGE = "Méthode introuvable";
        deliver_response($RETURN_CODE, $STATUS_MESSAGE, $matchingData);
        break;
}
/// Envoi de la réponse au Client
function deliver_response($status, $status_message, $data)
{
    /// Paramétrage de l'entête HTTP, suite
    header("HTTP/1.1 $status $status_message");
    /// Paramétrage de la réponse retournée
    $response['status'] = $status;
    $response['status_message'] = $status_message;
    $response['data'] = $data;
    /// Mapping de la réponse au format JSON
    $json_response = json_encode($response);
    echo $json_response;
}
