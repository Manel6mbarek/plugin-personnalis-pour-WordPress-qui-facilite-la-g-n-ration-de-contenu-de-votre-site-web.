<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'template_db';
$username = 'root';
$password = '';

// Vérifier si un ID a été passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de template invalide.");
}

$templateId = $_GET['id'];

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails du template
    $stmt = $pdo->prepare("SELECT * FROM template WHERE id = :id");
    $stmt->execute([':id' => $templateId]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$template) {
        die("Template non trouvé.");
    }

    // Vérifier s'il existe des soumissions pour ce template
    $checkSubmissions = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE template_id = :id");
    $checkSubmissions->execute([':id' => $templateId]);
    $submissionCount = $checkSubmissions->fetchColumn();

    // Traiter la soumission du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newContent = $_POST['content'] ?? '';
        $newName = $_POST['name'] ?? '';

        if (!empty($newContent) && !empty($newName)) {
            $pdo->beginTransaction();

            try {
                if ($submissionCount > 0) {
                    // Créer un nouveau template
                    $insertStmt = $pdo->prepare("INSERT INTO template (nom_fichier, contenu_html) VALUES (:name, :content)");
                    $insertStmt->execute([
                        ':name' => $newName,
                        ':content' => $newContent
                    ]);
                    $newTemplateId = $pdo->lastInsertId();

                    // Analyser le nouveau contenu pour obtenir les nouveaux champs
                    $newFields = parseTemplate($newContent, $newTemplateId);

                    // Insérer les nouveaux champs
                    $insertFieldStmt = $pdo->prepare("INSERT INTO template_fields (template_id, type, nom, label, required, identifiant, class_css, val_min, val_max, date_min, date_max, options, `condition`, limit_taille, format_fichier, valeur_par_defaut, attributs_supplementaires) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    foreach ($newFields as $field) {
                        $insertFieldStmt->execute(array_values($field));
                    }

                    $message = "Nouveau template créé avec l'ID: " . $newTemplateId;
                    $templateId = $newTemplateId; // Mettre à jour l'ID pour l'affichage
                } else {
                    // Mettre à jour le template existant
                    $updateStmt = $pdo->prepare("UPDATE template SET nom_fichier = :name, contenu_html = :content WHERE id = :id");
                    $updateStmt->execute([
                        ':name' => $newName,
                        ':content' => $newContent,
                        ':id' => $templateId
                    ]);

                    // Supprimer les anciens champs
                    $deleteFieldsStmt = $pdo->prepare("DELETE FROM template_fields WHERE template_id = :template_id");
                    $deleteFieldsStmt->execute([':template_id' => $templateId]);

                    // Insérer les nouveaux champs
                    $newFields = parseTemplate($newContent, $templateId);
                    $insertFieldStmt = $pdo->prepare("INSERT INTO template_fields (template_id, type, nom, label, required, identifiant, class_css, val_min, val_max, date_min, date_max, options, `condition`, limit_taille, format_fichier, valeur_par_defaut, attributs_supplementaires) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    foreach ($newFields as $field) {
                        $insertFieldStmt->execute(array_values($field));
                    }

                    $message = "Template existant mis à jour avec succès.";
                }

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Erreur lors de la mise à jour : " . $e->getMessage();
            }
        } else {
            $message = "Erreur : Le nom et le contenu du template sont requis.";
        }
    }

    // Récupérer les détails du template (potentiellement mis à jour)
    $stmt = $pdo->prepare("SELECT * FROM template WHERE id = :id");
    $stmt->execute([':id' => $templateId]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Fonction parseTemplate() ici (comme dans l'exemple précédent)

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer le Template</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Éditer le Template</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="name">Nom du Template:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($template['nom_fichier']); ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Contenu HTML:</label>
                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($template['contenu_html']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>