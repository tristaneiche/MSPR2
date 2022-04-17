<?php
class AntiBruteForce{
    function anti_brute_force(){
        
      $existence_ft = '';
        // Si le fichier existe, on le lit
        if(file_exists('../antibrute/'.$_POST['pseudo'].'.tmp')){
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
    }
}
