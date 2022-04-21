<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css" />    
    <link rel="shortcut icon" type="image/png" href="img/favicon.png">
</head>
<body>
    <div class="form">
        <form action="" method="post">
            <p id="idFaux" style="display: none; color:red; font-size: 12px;">Le pseudo ou le mot de passe est incorrect, le compte n'a pas été trouvé</p>
            <input type="text" placeholder="pseudo" name="pseudo"/>
            <p id="pseudoVide" style="display: none; color:red; font-size: 12px;">Le champ pseudo est vide</p>
            <input type="password" placeholder="mot de passe" name="mdp"/>
            <p id="mdpVide" style="display: none; color:red; font-size: 12px;">Le champ mot de passe est vide</p>
            <input class="button" type="submit" name="submit" value="Connexion" />
            <p id="script" style="display: none; color:red; font-size: 12px;">Trop de tentatives d'authentification aujourd'hui. Revenez demain</p>
            <p id="ip" style="display: none; color:red; font-size: 12px;">Votre connexion provient d’un pays étranger, la connexion est bloquée</p>
            <p id="browser" style="display: none; color:red; font-size: 12px;">Votre connexion provient d’un nouveau navigateur, veuillez confirmer votre identité par mail</p>
          </form>
        <p class="message">Un problème? <a href="mailto:lechatelet52@gmail.com">Contactez l'administrateur</a></p>
    </div>
</body>
</html>

<?php
session_start();
$session_id = session_id();
if(isset($_POST['submit'])){
    if(!empty($_POST['pseudo']) AND !empty($_POST['mdp'])){
            $ldap_host = "ldap://192.168.1.76";
            $base_dn = "CN=Administrateur, CN=Users, DC=therealchatelet, DC=net";
            $pseudo_ldap = $_POST["pseudo"];
            $pseudo_dn = 'therealchatelet.net' . "\\" . $pseudo_ldap;
            $mdp_ldap = $_POST["mdp"];
            $ldapPort = 389; 
            $connect = ldap_connect($ldap_host, $ldapPort);
            ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
            $bind = @ldap_bind($connect, $pseudo_ldap, $mdp_ldap);


            if ($bind) {
                echo "réussi";
            } else {
                echo "fail";   
            }


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
        $mysqli = mysqli_connect("localhost", "lechatelet", "dove", "Users");
        $verifications = mysqli_query($mysqli,'SELECT * FROM user WHERE pseudo = \''.mysqli_real_escape_string($mysqli, $_POST['pseudo']).'\' ');
          $data_verif = mysqli_fetch_assoc($verifications);
            
            if ( $bind == FALSE ){// si BIND==FALSE, mdp faux
                ?>
                <script>
                var x = document.getElementById("idFaux");
                if (x.style.display === "none") {
                x.style.display = "block";
                } else {
                x.style.display = "none";
                }
                </script> <?php
                
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
            
            elseif ( $bind == TRUE ){
                echo "bind true";
                $filter="(&(sAMAccountName=" . $pseudo_ldap . "))";
                $result = ldap_search($connect, "DC=therealchatelet, DC=net", $filter);
                
                ldap_sort($connect,$result,"sn");
                $data = ldap_get_entries($connect, $result);

                echo " " . $result;
                if($result === -1){
                    echo "Erreur";
                }elseif($result === FALSE){
                    echo "mdp faux";
                }elseif($result === "Resource id #6"){
                    //contenu 
                    echo "Connected";
                    require_once('DetectBrowser/detectBrowser.php');
                    $detect_browser = new detectBrowser();
                    $browser = $detect_browser->detect_browser();
                    if($data_verif['navigateur'] == $browser){
                        echo " browser" ;
                        require_once('DetectIp/detectIp.php');
                        $detect_ip = new detectIp();
                        $ip = $detect_ip->detect_ip();
                        $details = file_get_contents("https://ipinfo.io/.$ip.?token=e9eb8ad2a16715");
                        $json = json_decode($details);
                        $country = $json->country;
                        
                        if($country == "FR"){
                            echo " FR" ;
                            $_SESSION['pseudo'] = $data_verif['pseudo'];  
                            header("Location: " . 'A2F/index.php', true, 301);
                        }else{
                                        $dest = ($data_verif['email']);
                                        $objet="Mauvaise IP";
                                        $message="Madame, Monsieur,
    Suite à une récente connexion sur votre compte Le Chatelet, nous avons constaté une activité suspecte. La connexion s'est opérée depuis un nouveau navigateur.";
                                        $entetes="From: selma.eljabri@epsi.fr";
                                        mail($dest, $objet, $message, $entetes);
                                    ?>
                                        <script>
                                        var x = document.getElementById("ip");
                                        if (x.style.display === "none") {
                                        x.style.display = "block";
                                        } else {
                                        x.style.display = "none";
                                        }
                                        </script>   <?php
                                }
                            }else{
                                    $pseudo = $_POST['pseudo'];
                                    include_once 'Double_Co_Mail/randomizer.php';
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
                                    $message='Afin de valider votre compte merci de cliquer sur ce lien https://therealchatelet.fr?pseudo='.urlencode($pseudo).'&key_confirm='.urlencode($key);
                                    $entetes="MIME-Version: 1.0\r\nContent-type:text/html;charset=iso-8859-1\r\n";
                                    $entetes.="From: selma.eljabri@epsi.fr";
                                    mail($dest, $objet, $message, $entetes);
                                    ?>
                                        <script>
                                        var x = document.getElementById("browser");
                                        if (x.style.display === "none") {
                                        x.style.display = "block";
                                        } else {
                                        x.style.display = "none";
                                        }
                                        </script>   <?php
                
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
                                                    echo "compte activé";
                                                    header("Location: Index/index.php");
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
                            ldap_close($connect);  
                    }  
                }else{
                    echo "bind qui bug";
                }
    // S'il y a déjà eu 30 tentatives dans la journée, on affiche un message d'erreur
            }else{
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
    
    }else{?>
        <script>
        var x = document.getElementById("idFaux");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
        </script>   <?php
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
}


?>