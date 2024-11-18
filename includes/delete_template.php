<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'template_db';
$username = 'root';
$password = '';

$pdo = null;

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validation de l'ID du template
    $templateId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$templateId) {
        throw new Exception("ID de template invalide.");
    }

    error_log("Tentative de suppression du template ID: $templateId");

    // Vérification de l'existence du template
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM template WHERE id = :template_id");
    $stmt->execute([':template_id' => $templateId]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("Le template avec l'ID $templateId n'existe pas.");
    }

    // Début de la transaction
    $pdo->beginTransaction();
    error_log('Transaction démarrée.');

    // Supprimer les données de soumission associées
    $stmt = $pdo->prepare("DELETE sd FROM submission_data sd
                           INNER JOIN submissions s ON sd.submission_id = s.id
                           WHERE s.template_id = :template_id");
    $stmt->execute([':template_id' => $templateId]);

    // Supprimer les soumissions associées
    $stmt = $pdo->prepare("DELETE FROM submissions WHERE template_id = :template_id");
    $stmt->execute([':template_id' => $templateId]);

    // Supprimer les champs du template
    $stmt = $pdo->prepare("DELETE FROM template_fields WHERE template_id = :template_id");
    $stmt->execute([':template_id' => $templateId]);

    // Supprimer le template lui-même
    $stmt = $pdo->prepare("DELETE FROM template WHERE id = :template_id");
    $stmt->execute([':template_id' => $templateId]);

    // Commit de la transaction
    $pdo->commit();
    error_log('Transaction commit avec succès.');

    echo json_encode(['success' => true, 'message' => 'Template supprimé avec succès']);

} catch (PDOException $e) {
    error_log('Erreur PDO : ' . $e->getMessage());
    if ($pdo && $pdo->inTransaction()) {
        error_log('Transaction rollback en cours.');
        $pdo->rollBack();
    } else {
        error_log('Aucune transaction active à rollback.');
    }
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('Erreur générale : ' . $e->getMessage());
    if ($pdo && $pdo->inTransaction()) {
        error_log('Transaction rollback en cours.');
        $pdo->rollBack();
    } else {
        error_log('Aucune transaction active à rollback.');
    }
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}