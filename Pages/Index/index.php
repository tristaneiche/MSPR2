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
    include '../connexion.php';
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
    header("Location: " . '../connexion.php', true, 301);
}
} else { 
?>
    <h1>Bienvenue <?php echo $_SESSION['pseudo']; ?>!</h1>

    <p>Contenu sensible</p>
    <p class="message"><a href="../Deconnexion/logout.php">Déconnexion</a></p>
<?php
    require_once('../DetectIp/detectIp.php');
    $detect_ip = new detectIp();
    $ip = $detect_ip->detect_ip();
    $details = file_get_contents("https://ipinfo.io/.$ip.?token=e9eb8ad2a16715");
    $json = json_decode($details);

    $ip = $json->ip;
    $city = $json->city;
    $postal = $json->postal;
    $region = $json->region;
    $country = $json->country;
    $timezone = $json->timezone;
}
?>
    </div>
    <div class="ip">
        <p><?php echo "IP : " . $ip; ?></p>
        <p><?php echo "Ville : " . $city; ?></p>
        <p><?php echo "Code Postal : " . $postal; ?></p>
        <p><?php echo "Région : " . $region; ?></p>
        <p><?php echo "Pays : " . $country; ?></p>
        <p><?php echo "Timezone : " . $timezone; ?></p>
    </div>
</body>
</html>