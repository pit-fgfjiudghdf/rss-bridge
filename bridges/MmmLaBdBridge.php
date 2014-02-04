<?php
/**
*
* @name Mmm La Bd
* @description Mmm La Bd via rss-bridge
* @update 17/10/2013
*/
class MmmLaBdBridge extends BridgeAbstract{

    public function collectData(array $param){
        
        $nbItem = 10;    //nombre d'item dans le flux. ATTENTION, hormis  la dernière image, toutes les requêtes sont simultanées! Vérifiez donc que votre serveur supporte ce nombre de requête simultanées
        
        $html = file_get_html('http://hmm-la-bd.eu/') or $this->returnError('Could not request mmm la bd.', 404);
        $nb = 0;
        foreach($html->find('div#main_page') as $element) {
            $a = $element->find('a', 5);
            $img = $a->find('img', 0);
            
            $item = new Item();
            $item->content = $img->title . "<br>\n" . $a->innertext;
            $item->uri =  $a->href;
            $item->title = $img->alt;
            $nb = intval($img->alt);    //l'attibut alt contient quelque chose du genre "240 - Ascenseur" et je ne veut que le "240". 
            $this->items[] = $item;
        }
        
        
       if($nb > $nbItem)
       {
           // Création du gestionnaire multiple
           $mh = curl_multi_init();
           $timeoutCurl = 30;
           //servira pour les traitements qui seront fait une fois que tous les sites auront répondus
           $listeCurl = array();

           for($i = ($nb - 1); $i > ($nb - ($nbItem +1)); $i--)
           {
                   $uri = 'http://hmm-la-bd.eu/' . $i;
                   $ch = curl_init($uri);
//                   curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
                   curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                   curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutCurl);
                   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeoutCurl);
                   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                       
                   $listeCurl[] = array("ch"=>$ch, "uri"=>$uri);
                       
                   curl_multi_add_handle($mh,$ch);
           }

           $running = null;
           do
           {
               curl_multi_exec($mh,$running);
           }
           while($running > 0);

           //traitement des résultats
           foreach($listeCurl as $lc)
           {
               $xmlStr = curl_multi_getcontent($lc["ch"]);
               $html = str_get_html($xmlStr) or $this->returnError('Could not request mmm la bd.', 404);
               $nb = 0;
               foreach($html->find('div#main_page') as $element)
               {
                   $a = $element->find('a', 5);
                   $img = $a->find('img', 0);

                   $item = new Item();
                   $item->content = $img->title . "<br>\n" . $a->innertext;
                   $item->uri =  $lc["uri"];
                   $item->title = $img->alt;
                   $this->items[] = $item;
               }
               curl_multi_remove_handle($mh,$lc["ch"]);
           }
           curl_multi_close($mh);
       }
    }

    public function getName(){
        return 'Mmm La Bd';
    }

    public function getURI(){
        return 'http://hmm-la-bd.eu/';
    }

    public function getDescription(){
        return 'Mmm La Bd via rss-bridge';
    }

    public function getCacheDuration(){
        return 54000; // 15 hours
    }
}
?>
