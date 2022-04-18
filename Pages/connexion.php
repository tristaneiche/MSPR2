<?php

$ldap_host = "therealchatelet.fr";
$base_dn = "o=LeChatelet,c=FR";

$user = "cn=".$_POST['pseudo'];
$password = $_POST['mdp']; 

$admin="admin";
$membres="membres";

$connect = ldap_connect($ldap_host)or exit("Connexion au serveur LDAP echoué");
		  
ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);

$read = ldap_search($connect,$base_dn, $user)
     or exit("Erreur lors de la recherche");
$info = ldap_get_entries($connect, $read);
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="form">
        <form action="#" method="post">
            <p id="idFaux" style="display: none; color:red; font-size: 12px;">Le pseudo ou le mot de passe est incorrect, le compte n'a pas été trouvé</p>
            <input type="text" placeholder="pseudo" name="pseudo"/>
            <p id="pseudoVide" style="display: none; color:red; font-size: 12px;">Le champ pseudo est vide</p>
            <input type="password" placeholder="mot de passe" name="mdp"/>
            <p id="mdpVide" style="display: none; color:red; font-size: 12px;">Le champ mot de passe est vide</p>
            <input class="button" type="submit" name="submit" value="Connexion" />
            <p id="script" style="display: none; color:red; font-size: 12px;">Trop de tentatives d\'authentification aujourd\'hui. Revenez demain</p>
            <p id="ip" style="display: none; color:red; font-size: 12px;">Votre adresse IP n'est pas très française</p>
          </form>
        <p class="message">Pas enregistré? <a href="mailto:adresse@serveur.com">Contactez l'administrateur</a></p>
    </div>
</body>
</html>

<?php
session_start();
$session_id = session_id();
if(isset($_POST['submit'])){
    if(!empty($_POST['pseudo']) AND !empty($_POST['mdp'])){
        $existence_ft = '';
        // Si le fichier existe, on le lit
        if(file_exists('AntiBruteForce/antibrute/'.$_POST['pseudo'].'.tmp')){
            // On ouvre le fichier
            $fichier_tentatives = fopen('AntiBruteForce/antibrute/'.$_POST['pseudo'].'.tmp', 'r+');
            // On récupère son contenu dans la variable $infos_tentatives
            $contenu_tentatives = fgets($fichier_tentatives);
            // On découpe le contenu du fichier pour récupérer les informations
            $infos_tentatives = explode(';', $contenu_tentatives);
            // Si la date du fichier est celle d'aujourd'hui, on récupère le nombre de tentatives
            if($infos_tentatives[0] == date('d/m/Y')){
                $tentatives = $infos_tentatives[1];
            }
            // Si la date du fichier est dépassée, on met le nombre de tentatives à 0 et $existence_ft à 2
            else{
                $existence_ft = 2;
                $tentatives = 0; // On met la variable $tentatives à 0
            }
        }
        // Si le fichier n'existe pas encore, on met la variable $existence_ft à 1 et on met les $tentatives à 0
        else{
            $existence_ft = 1;
            $tentatives = 0;
        }
        // S'il y a eu moins de 5 identifications ratées dans la journée, on laisse passer
    if($tentatives < 5){

        $mysqli = mysqli_connect("localhost", "root", "", "mspr");

        $verifications = mysqli_query($mysqli,'SELECT * FROM user WHERE pseudo = \''.mysqli_real_escape_string($mysqli, $_POST['pseudo']).'\' ');
          $data_verif = mysqli_fetch_assoc($verifications);

        if ( preg_match("!".$membres."!",$info[0]["dn"]) ) // si le user trouvé est membre
            {
            $bind = ldap_bind($connect,$info[0]["dn"],$password);
            if ( $bind == FALSE ){// si BIND==FALSE, mdp faux
                echo "La connexion provient d'un compte membre mais le mdp est erroné";
                else{

                    // Si la variable $tentatives est sur le point de passer à 5, on en informe l'administrateur du site
                    if($tentatives == 4){
                        $email_administrateur = 'selma.eljabri1@gmail.com';
                        $sujet_notification = 'Un compte membre va atteindre son quota';
                        $message_notification = 'Un des comptes va atteindre le quota de mauvais mots de passe journalier :';
                        $message_notification .= $data_verif['pseudo'].' - '.$_SERVER['REMOTE_ADDR'].' - '.gethostbyaddr($_SERVER['REMOTE_ADDR']);

                        mail($email_administrateur, $sujet_notification, $message_notification);
                    }

                    fseek($fichier_tentatives, 11); // On place le curseur juste devant le nombre de tentatives
                    fputs($fichier_tentatives, $tentatives + 1); // On ajoute 1 au nombre de tentatives
                }
            }
            
            elseif ( $bind == TRUE ){
                        require_once('../detectBrowser.php');
                        $detect_browser = new detectBrowser();
                        $browser = $detect_browser->detect_browser();
                        if($data_verif['navigateur'] == $browser){
                            require_once('../detectIp.php');
                            $detect_ip = new detectIp();
                            $ip = $detect_ip->detect_ip();
                            if($data_verif['ip'] = $ip){
                                $_SESSION['pseudo'] = $data_verif['pseudo'];  
                                header("Location: " . '../A2F/index.php', true, 301);
                                //include '../Index/index.php';
                            }else{
                                $french_ips = array("/^102\.12\.[0-9]{1,3}\.[0-9]{1,3}/","/^201\.2\.[0-9]{1,3}\.[0-9]{1,3}/");
                                $ip_is_french=false;
                                if (preg_match($french_ips[$i],$_SERVER['REMOTE_ADDR'])==1){
                                    $ip_is_french=true;
                                }    
                                if($ip_is_french){
                                    $dest = ($data_verif['email']);
                                    $objet="Mauvaise IP";
                                    $message="Madame, Monsieur,
                                    Suite à une récente connexion sur votre compte Le Chatelet, nous avons constaté une activité suspecte. La connexion s'est opérée depuis un nouveau navigateur. 
                                    Veuillez confirmer votre tentative de connexion";
                                    $entetes="From: selma.eljabri@epsi.fr";
                                    mail($dest, $objet, $message, $entetes);
                                }else{?>
                                    <script>
                                    var x = document.getElementById("ip");
                                    if (x.style.display === "none") {
                                    x.style.display = "block";
                                    } else {
                                    x.style.display = "none";
                                    }
                                    </script>   <?php
                                }
                            }
                        }else{
                                $pseudo = $_POST['pseudo'];
                                include_once '../Double_Co_Mail/randomizer.php';
                                $random = new randomizer();
                                $key = $random->str_random(40);
            
                                $dbh = new PDO('mysql:host=localhost;dbname=mspr', 'root', '');
            
                                $user_id = $data_verif['id'];
                                $bdd = $dbh->prepare("UPDATE user SET key_confirm=:key_confirm WHERE pseudo like :pseudo");
                                $bdd->bindParam(':key_confirm', $key);
                                $bdd->bindParam(':pseudo', $pseudo);
                                $bdd->execute();
            
                                $bdd2 = $dbh->prepare("UPDATE user SET confirmed=1 WHERE pseudo like :pseudo");
                                $bdd2->bindParam(':pseudo', $pseudo);
                                $bdd2->execute();
            
                                $_SESSION['email'] = $data_verif['email'];
                                $dest = ($_SESSION['email']);
                                $objet="Mauvais navigateur";
                                $message='Afin de valider votre compte merci de cliquer sur ce lien http://127.0.0.1/MSPR/Pages/Connexion/connexion.php?pseudo='.urlencode($pseudo).'&key_confirm='.urlencode($key);
                                $entetes="MIME-Version: 1.0\r\nContent-type:text/html;charset=iso-8859-1\r\n";
                                $entetes.="From: selma.eljabri@epsi.fr";
                                mail($dest, $objet, $message, $entetes);
            
                                if(isset($_GET['key_confirm'])){
                                    $keys = $_GET['key_confirm'];
                                    $req = mysqli_query($mysqli, 'SELECT * FROM user WHERE id = '.$user_id);
                                        if($req->execute(array(':pseudo'=> $pseudo)) && $row = $req->fetch()){
                                        $keybdd = $row['key_confirm'];
                                        $confirmed = $row['confirmed'];
                                        }
                
                                        if($confirmed == '1'){
                                        echo "compte deja actif";
                                        }else{
                                        if ($keys = $keybdd){
                                            echo "compte activé, gg";
                                            header("Location: ../Index/index.php");
                                        }else{
                                            echo "erreur";
                                        }
                                        }
                                
                                    if(mail($dest, $objet, $message, $entetes)){
                                        echo "mail ok";
                                    }else{
                                        echo "pas ok";
                                    }
                
                                    exit();
                                }
                        }     
                }
            }// Si le pseudo n'existe pas
            else{
                ?>
                <script>
                var x = document.getElementById("idFaux");
                if (x.style.display === "none") {
                    x.style.display = "block";
                } else {
                    x.style.display = "none";
                }
                </script>   <?php
            }
    }
    // S'il y a déjà eu 30 tentatives dans la journée, on affiche un message d'erreur
    else{
        ?>
        <script>
        var x = document.getElementById("script");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }
        </script>   <?php
    }
    
    
    }
    if (empty($_POST['pseudo'])){
        ?>
        <script>
        var x = document.getElementById("pseudoVide");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }
        </script>   <?php
    }if(empty($_POST['mdp'])){
        ?>
        <script>
        var x = document.getElementById("mdpVide");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }
        </script>   <?php
    }
}

ldap_close($connect);
?>