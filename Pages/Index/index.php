<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Index</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="form">
    <div style="display:none;">
    <?php

//vérification si le membre est passé par la page de login :
    include '../Connexion/connexion.php';
?> </div>   <?php
if(!isset($_SESSION['pseudo'])) {
 
echo 'Désolé, vous devez être identifié pour accéder à cette page.';
?> 

<form action="#" method="post">
<input class="button" type="submit" name="retour" value="Retour" />

</form>

<?php

// si la variable de session login n'est pas enregistré : retour sur la page connexion.php
if(isset($_POST['retour'])){
    header("Location: " . '../Connexion/connexion.php', true, 301);
}

} else { // si tu es bien connecté.
?>
    <h1>Bienvenue <?php echo $_SESSION['pseudo']; ?>!</h1>

    <p>Contenu sensible</p>
    <p class="message"><a href="../Deconnexion/logout.php">Déconnexion</a></p>
<?php
}

?>


    </div>
</body>
</html>