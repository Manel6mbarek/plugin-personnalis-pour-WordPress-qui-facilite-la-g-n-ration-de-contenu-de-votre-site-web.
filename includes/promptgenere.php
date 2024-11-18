<?php
require '../vendor/autoload.php'; // Autoload Composer

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Créer l'application Slim
$app = AppFactory::create();

// Définir la route principale
$app->get('/', function (Request $request, Response $response, $args) {
    // Connexion à la base de données
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "template_db";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Exécution de la requête pour obtenir le dernier prompt spécifique
    $sql = "SELECT prompt_specifique FROM submissions ORDER BY date_soumission DESC LIMIT 1";
    $result = $conn->query($sql);

    $prompt = "";
    if ($result->num_rows > 0) {
        // Récupération du résultat
        $row = $result->fetch_assoc();
        $prompt = $row['prompt_specifique'];
    }
    $conn->close();

    if (!empty($prompt)) {
        // Configuration de l'API avec votre clé API
        $client = new GenAI(['api_key' => 'AI**************']);

        // Initialisation du modèle génératif
        $model = new GenerativeModel('gemini-1.0-pro-latest', $client);

        // Génération du contenu
        $response = $model->generateContent(['prompt' => $prompt]);
        $content = $response->text;

        // Sauvegarde du résultat dans file.txt
        file_put_contents('../file.txt', $content);

        // Remplacement des titres entre ** en balises HTML appropriées
        $content = str_replace('**', '<span class="highlight">', $content);
        $content = str_replace('**', '</span><br>', $content);
    } else {
        $content = "Aucun prompt spécifique trouvé dans la base de données.";
    }

    // Template HTML simple pour afficher le contenu
    $html_template = <<<HTML
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contenu Généré</title>
        <style>
            .highlight {
                font-weight: bold;
                color: orange;
            }
            pre {
                white-space: pre-wrap; /* Pour gérer les retours à la ligne */
            }
        </style>
    </head>
    <body>
        <pre>{{ content }}</pre>
    </body>
    </html>
    HTML;

    $html_template = str_replace('{{ content }}', htmlspecialchars($content), $html_template);
    $response->getBody()->write($html_template);
    return $response;
});

// Exécution de l'application
$app->run();
?>
