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
            <input class="button" type="submit" name="connexion" value="Connexion" />
            <p id="script" style="display: none; color:red; font-size: 12px;">Trop de tentatives d\'authentification aujourd\'hui. Revenez demain</p>
        </form>
        <p class="message">Pas enregistré? <a href="#">Contactez l'administrateur</a></p>
    </div>
</body>
</html>

<?php
session_start();
if(isset($_POST['connexion'])){
    if(!empty($_POST['pseudo']) AND !empty($_POST['mdp'])){
      $existence_ft = '';
      // Si le fichier existe, on le lit
      if(file_exists('antibrute/'.$_POST['pseudo'].'.tmp')){
          // On ouvre le fichier
          $fichier_tentatives = fopen('antibrute/'.$_POST['pseudo'].'.tmp', 'r+');
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
      // S'il y a eu moins de 30 identifications ratées dans la journée, on laisse passer
      if($tentatives < 30){
        $mysqli = mysqli_connect("localhost", "root", "", "mspr");

        $verifications = mysqli_query($mysqli,'SELECT * FROM user WHERE pseudo = \''.mysqli_real_escape_string($mysqli, $_POST['pseudo']).'\' ');
          $data_verif = mysqli_fetch_assoc($verifications);
          // Si le pseudo existe bien
          if(!empty($data_verif['pseudo'])){
            // Si le mot de passe est bon
            if($data_verif['mdp'] == trim($_POST['mdp'])){
              header('Location: ../Index/index.php');
            }
            // Si le mot de passe est faux
            else{
                // Si le fichier n'existe pas encore, on le créé
                if($existence_ft == 1){
                    $creation_fichier = fopen('antibrute/'.$data_verif['pseudo'].'.tmp', 'a+'); // On créé le fichier puis on l'ouvre
                    fputs($creation_fichier, date('d/m/Y').';1'); // On écrit à l'intérieur la date du jour et on met le nombre de tentatives à 1
                    fclose($creation_fichier); // On referme
                }
                // Si la date n'est plus a jour
                elseif($existence_ft == 2){
                    fseek($fichier_tentatives, 0); // On remet le curseur au début du fichier
                    fputs($fichier_tentatives, date('d/m/Y').';1 '); // On met à jour le contenu du fichier (date du jour;1 tentatives)
                }
                else{

                    // Si la variable $tentatives est sur le point de passer à 30, on en informe l'administrateur du site
                    if($tentatives == 29){
                        $email_administrateur = 'Email de administrateur du site';
                        $sujet_notification = 'Un compte membre a atteint son quota';
                        $message_notification = 'Un des comptes a atteint le quota de mauvais mots de passe journalier :';
                        $message_notification .= $data_verif['pseudo'].' - '.$_SERVER['REMOTE_ADDR'].' - '.gethostbyaddr($_SERVER['REMOTE_ADDR']);
  
                        mail($email_administrateur, $sujet_notification, $message_notification);
                    }

                    fseek($fichier_tentatives, 11); // On place le curseur juste devant le nombre de tentatives
                    fputs($fichier_tentatives, $tentatives + 1); // On ajoute 1 au nombre de tentatives
                }
            }
          }
          // Si le pseudo n'existe pas
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
      if($existence_ft != 1){
        fclose($fichier_tentatives);
      }
    }if (empty($_POST['pseudo'])){
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
}else{
  echo '';
}
?>
