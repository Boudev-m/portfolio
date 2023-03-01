<?php
// ACCOUNT CONTROLLER

namespace App\Controllers;

use PDO;
use App\Models\Account;
// use App\Controllers\DatabaseConnection;      // Pas besoin car DatabaseConnection est dans le même namespace que AccountController

if (session_status() === 1) session_start();

// require_once(__DIR__ . "/DatabaseConnection.php");
// require_once(__DIR__ . "../../Models/Account.php");

class AccountController
{
    // LOGIN
    function login(string $email, string $password)
    {
        // require_once(__DIR__ . DIRECTORY_SEPARATOR . "GeneralController.php");

        // Vérifie le formulaire
        $this->checkLoginForm($_SERVER['REQUEST_URI']);

        // formatage du mail
        $email = trim(strtolower($email));

        // Vérifie l'email de l'admin (unique). le champ email possède une clé unique dans la BDD
        // Préparation de la requête : vérifie si l'email est présente 
        $sql = "
            SELECT * FROM account WHERE email = :email
        ";
        $statement = (new DatabaseConnection)->getConnection()->prepare($sql);
        $statement->bindParam(":email", $email);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, Account::class);
        $account = $statement->fetch();
        // Vérifie si le compte existe
        $account ?: GeneralController::redirectWithError($_SERVER['REQUEST_URI'], 'Aucun compte ne correspond à cette email.');
        // Vérifie le mdp
        password_verify(trim($password), $account->password) ?: GeneralController::redirectWithError($_SERVER['REQUEST_URI'], 'Mot de passe incorrect.');

        // Connexion réussie
        // on sauvegarde des données dans la session (qui autorisent l'accès au back-office)
        $_SESSION['isLogged'] = true;
        $_SESSION['role'] = 'admin';
        GeneralController::redirectWithSuccess('../admin/dashboard', "Vous êtes connecté.");
    }

    // LOGOUT
    function logout()
    {
        // supprime la session courante et toutes les données en session
        session_destroy();
        session_start();
        // require_once(__DIR__ . DIRECTORY_SEPARATOR . "GeneralController.php");
        GeneralController::redirectWithSuccess('http://localhost/portfolio', 'Vous êtes déconnecté.');
    }

    // READ ACCOUNT
    function read()
    {
        // Préparation de la requête : Récupère la ligne correspondant à l'id dans la table user
        $sql = "
            SELECT id_account, email, hidden_password FROM account
        ";

        $statement = (new DatabaseConnection)->getConnection()->prepare($sql);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, Account::class);
        $result = $statement->fetch();
        return $result;
    }

    // UPDATE EMAIL
    function updateEmail()
    {
        // require_once(__DIR__ . DIRECTORY_SEPARATOR . "GeneralController.php");
        $this->checkUpdateEmailForm($_SERVER['REQUEST_URI']);

        // Vérifie si l'email actuel est associé au compte
        $sql = "
            SELECT * FROM account WHERE email = :email
        ";

        $statement = (new DatabaseConnection)->getConnection()->prepare($sql);
        $statement->bindParam(":email", $_POST['email']);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, Account::class);
        $statement->fetch() ?: GeneralController::redirectWithError($_SERVER['REQUEST_URI'], 'L\'email actuel ne correspond à aucun compte.');

        // Formatte le nouvel email
        $email = strip_tags(trim(strtolower($_POST["newEmail"])));

        // Requête SQL pour modifier le compte
        $sql = "
            UPDATE account SET email = :email
        ";

        $statement = (new DatabaseConnection)->getConnection()->prepare($sql);
        $statement->bindParam(":email", $email);
        $statement->execute();

        GeneralController::redirectWithSuccess('./', 'L\'email a été modifié.');
    }

    // UPDATE PASSWORD
    function updatePassword()
    {
        // require_once(__DIR__ . DIRECTORY_SEPARATOR . "GeneralController.php");
        $this->checkUpdatePasswordForm($_SERVER['REQUEST_URI']);

        // Vérifie le mdp
        $sql = "
            SELECT * FROM account
        ";

        $statement = (new DatabaseConnection)->getConnection()->prepare($sql);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, Account::class);
        $account = $statement->fetch();

        // Vérifie le mdp
        password_verify(trim($_POST['password']), $account->password) ?: GeneralController::redirectWithError($_SERVER['REQUEST_URI'], 'Le mot de passe actuel est incorrect.');

        $password = strip_tags(trim($_POST['newPassword']));
        // le hiddenPassword sera affiché et servira de rappel à l'utilisateur
        $hiddenPassword = str_repeat('*', strlen($password) - 2) . substr($password, -2);
        $password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // Requête SQL pour modifier le mdp
        $sql = "
            UPDATE account SET
            password = :password,
            hidden_password = :hidden_password
        ";

        $statement = (new DatabaseConnection)->getConnection()->prepare($sql);
        $statement->bindParam(":password", $password);
        $statement->bindParam(":hidden_password", $hiddenPassword);
        $statement->execute();

        GeneralController::redirectWithSuccess('./', 'Le mot de passe a été modifié.');
    }

    // CHECK LOGIN FORM (vérifie la validité du formulaire, prend le chemin de redirection en param, en cas d'invalidité du form)
    function checkLoginForm($redirectionPath)
    {
        // Vérifie si tous les champs du formulaire sont remplis
        if (!$_POST['email']) {
            GeneralController::redirectWithError($redirectionPath, 'L\'email est obligatoire.');
        }
        if (!$_POST['password']) {
            GeneralController::redirectWithError($redirectionPath, 'Le mot de passe est obligatoire.');
        }

        // Vérifie la longueur des caractères saisies
        if (strlen($_POST['email']) > 255) {
            GeneralController::redirectWithError($redirectionPath, 'L\'email doit comporter entre 1 et 255 caractères.');
        }
        if (strlen($_POST['password']) > 255) {
            GeneralController::redirectWithError($redirectionPath, 'Le mot de passe doit comporter entre 1 et 255 caractères.');
        }

        // Vérifie le format d'écriture de l'email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            GeneralController::redirectWithError($redirectionPath, 'Email non valide.');
        }
    }

    // CHECK UPDATE EMAIL FORM
    function checkUpdateEmailForm($redirectionPath)
    {
        // Vérifie si tous les champs du formulaire sont remplis
        if (!$_POST['email']) {
            GeneralController::redirectWithError($redirectionPath, 'L\'email est obligatoire.');
        }
        if (!$_POST['newEmail']) {
            GeneralController::redirectWithError($redirectionPath, 'Le nouvel email est obligatoire.');
        }
        if (!$_POST['newEmailConfirmation']) {
            GeneralController::redirectWithError($redirectionPath, 'La confirmation du nouvel email est obligatoire.');
        }

        // Vérifie la longueur des caractères saisies
        if (strlen($_POST['email']) > 255) {
            GeneralController::redirectWithError($redirectionPath, 'L\'email doit comporter entre 1 et 255 caractères.');
        }
        if (strlen($_POST['newEmail']) > 255) {
            GeneralController::redirectWithError($redirectionPath, 'Le nouvel email doit comporter entre 1 et 255 caractères.');
        }

        // Vérifie le format d'écriture de l'email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            GeneralController::redirectWithError($redirectionPath, 'Email non valide.');
        }
        // Vérifie le format d'écriture de l'email
        if (!filter_var($_POST['newEmail'], FILTER_VALIDATE_EMAIL)) {
            GeneralController::redirectWithError($redirectionPath, 'Nouvel email non valide.');
        }

        // Vérifie que l'email de confirmation correspond au 1er email saisi
        if ($_POST['newEmailConfirmation'] !== $_POST['newEmail']) {
            GeneralController::redirectWithError($redirectionPath, 'L\'email de confirmation doit être identique au nouvel email saisi.');
        }
    }

    // CHECK UPDATE PASSWORD FORM
    function checkUpdatePasswordForm($redirectionPath)
    {
        // Vérifie si tous les champs du formulaire sont remplis
        if (!$_POST['password']) {
            GeneralController::redirectWithError($redirectionPath, 'Le mot de passe actuel est obligatoire.');
        }
        if (!$_POST['newPassword']) {
            GeneralController::redirectWithError($redirectionPath, 'Le nouveau mot de passe est obligatoire.');
        }
        if (!$_POST['newPasswordConfirmation']) {
            GeneralController::redirectWithError($redirectionPath, 'La confirmation du nouveau mot de passe est obligatoire.');
        }

        // Vérifie la longueur des caractères saisies
        if (strlen($_POST['password']) < 8 || strlen($_POST['password']) > 255) {
            GeneralController::redirectWithError($redirectionPath, 'Le mot de passe actuel doit comporter entre 8 et 255 caractères.');
        }
        if (strlen($_POST['newPassword']) < 8 || strlen($_POST['newPassword']) > 255) {
            GeneralController::redirectWithError($redirectionPath, 'Le nouveau mot de passe doit comporter entre 8 et 255 caractères.');
        }

        // Vérifie que l'email de confirmation correspond au 1er email saisi
        if ($_POST['newPasswordConfirmation'] !== $_POST['newPassword']) {
            GeneralController::redirectWithError($redirectionPath, 'Le mot de passe de confirmation doit être identique au nouveau mot de passe saisi.');
        }
    }
}
