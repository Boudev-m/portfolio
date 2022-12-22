<!-- TABLEAU DE BORD (ADMIN UNIQUEMENT) -->

<?php include '../assets/inc/back/head.php' ?>
<title>Tableau de bord</title>

<?php require '../core/authentificationAdmin.php' ?>


<!-- COUNT USERS FROM DB -->
<?php
// Fichier databaseConnexion.php requis pour la connexion à la BDD
require '../core/databaseConnexion.php';

// Préparation de la requête : Récupère la ligne correspondant à l'id dans la table user
$sql = "SELECT * FROM user";

// Execution de la requête avec les params de connexion et sauvegarde la reponse dans $query
$query = mysqli_query($connexion, $sql) or exit(mysqli_error($connexion));

// Met les données sous forme de tableau associatif et exploitable
$usersLength = mysqli_num_rows($query);
?>

<?php include '../assets/inc/back/header.php' ?>

<main>
    <div class="bg-dark mb-2" style="border: 2px solid #666;">
        <h4 class="text-center pt-2">TABLEAU DE BORD</h4>
    </div>
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    };
    ?>
    <div class="row bg-dark mx-auto text-center justify-content-center py-4" style="border: 2px solid #666;">
        <div class="col-5 m-3 border pt-3 pb-2" style="background:#345">
            <h5>Nombre d'utilisateurs inscris</h5>
            <p class="fs-3"><?= $usersLength ?></p>
            <p><a href="./manageUsers.php" class="btn btn-secondary" style="transition:300ms" onmouseout="this.style.filter='invert(0)';this.style.transform='scale(1)'" onmouseover="this.style.filter='invert(1)';this.style.transform='scale(1.1)'">Voir la liste</a></p>
        </div>
        <div class="col-5 m-3 border pt-3 pb-2" style="background:#435">
            <h5>Nombre de compétences</h5>
            <p class="fs-3">5</p>
            <p><a href="./manageSkills.php" class="btn btn-secondary" style="transition:300ms" onmouseout="this.style.filter='invert(0)';this.style.transform='scale(1)'" onmouseover="this.style.filter='invert(1)';this.style.transform='scale(1.1)'">Voir la liste</a></p>
        </div>
        <div class="col-5 m-3 border pt-3 pb-2" style="background:#543">
            <h5>Nombre de réalisations</h5>
            <p class="fs-3">12</p>
            <p><a href="./manageProjects.php" class="btn btn-secondary" style="transition:300ms" onmouseout="this.style.filter='invert(0)';this.style.transform='scale(1)'" onmouseover="this.style.filter='invert(1)';this.style.transform='scale(1.1)'">Voir la liste</a></p>
        </div>
        <div class="col-5 m-3 border pt-3 pb-2" style="background:#354">
            <h5>Nombre de messages</h5>
            <p class="fs-3">230</p>
            <p><a href="./manageMessages.php" class="btn btn-secondary" style="transition:300ms" onmouseout="this.style.filter='invert(0)';this.style.transform='scale(1)'" onmouseover="this.style.filter='invert(1)';this.style.transform='scale(1.1)'">Voir la liste</a></p>
        </div>
    </div>
</main>

<?php include '../assets/inc/back/footer.php' ?>