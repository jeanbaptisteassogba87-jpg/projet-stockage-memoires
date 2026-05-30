
<?php
 require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../dao/UtilisateurDAO.php';

$dao = new UtilisateurDAO();

$utilisateurs = $dao->getAllUtilisateurs();

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion des comptes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="container mt-5">

    <h2 class="mb-4">Créer un utilisateur</h2>

    <?php if (!empty($_GET['success'])): ?>

        <div class="alert alert-success">
            Utilisateur créé avec succès.
        </div>

    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>

        <div class="alert alert-danger">
            Erreur lors de la création.
        </div>

    <?php endif; ?>



    <form method="POST" 
      action="../../controllers/TechnicienController.php">
        <input type="hidden"
               name="action"
               value="creer_utilisateur">



        <div class="mb-3">

            <label>Nom</label>

            <input type="text"
                   name="nom"
                   class="form-control"
                   required>

        </div>



        <div class="mb-3">

            <label>Email</label>

            <input type="email"
                   name="email"
                   class="form-control"
                   required>

        </div>



        <div class="mb-3">

            <label>Mot de passe</label>

            <input type="text"
                   name="mot_de_passe"
                   class="form-control"
                   required>

        </div>



        <div class="mb-3">

            <label>Rôle</label>

            <select name="role"
                    class="form-select">

                <option value="technicien">Technicien</option>

                <option value="professeur">Professeur</option>

                <option value="directeur">Directeur</option>

                <option value="etudiant">Étudiant</option>

            </select>

        </div>



        <div class="mb-3">

            <label>ID Centre</label>

            <input type="number"
                   name="centre_id"
                   class="form-control"
                   required>

        </div>



        <button type="submit"
                class="btn btn-primary">

            Créer le compte

        </button>

    </form>


<hr class="my-5">

<h3>Liste des utilisateurs</h3>

<table class="table table-bordered table-striped mt-3">

    <thead>

        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actif</th>
            <th>Action</th>
        </tr>

    </thead>

    <tbody>

        <?php foreach ($utilisateurs as $user): ?>

            <tr>

                <td><?= $user['id_utilisateur'] ?></td>

                <td><?= $user['nom'] ?></td>

                <td><?= $user['email'] ?></td>

                <td><?= $user['role'] ?></td>

                <td>
                    <?= $user['est_actif'] ? 'Oui' : 'Non' ?>
                </td>

                <td>

                    <?php if ($user['est_actif']): ?>

                        <form method="POST"
                              action="../../controllers/TechnicienController.php">

                            <input type="hidden"
                                   name="action"
                                   value="desactiver_utilisateur">

                            <input type="hidden"
                                   name="id_utilisateur"
                                   value="<?= $user['id_utilisateur'] ?>">

                            <button type="submit"
                                    class="btn btn-danger btn-sm">

                                Désactiver

                            </button>

                        </form>

                    <?php else: ?>

                        <span class="text-danger">
                            Désactivé
                        </span>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endforeach; ?>

    </tbody>

</table>
</body>
</html>
