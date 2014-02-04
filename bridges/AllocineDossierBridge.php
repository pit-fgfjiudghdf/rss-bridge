<?php
/**
*
* @name Allo Cine : les dossiers
* @description Allo Cine : dossiers via rss-bridge
* @update 07/11/2013
*/
class AllocineDossierBridge extends BridgeAbstract{

    private $_URL = "http://www.allocine.fr/dossiers/cinema/rubrique-23144/";
    private $_NOM = "Les dossiers";
    
    public function collectData(array $param){
        $html = file_get_html($this->_URL) or $this->returnError('Could not request Allo cine.', 404);
        
        foreach($html->find('ul.list_img_side_content li') as $element)
        {
            $item = new Item();
            
            $content = $element->innertext;
            $content = str_replace('src="/', 'src="http://www.allocine.fr/',$content);
            $content = str_replace('href="/', 'href="http://www.allocine.fr/',$content);
            $content = str_replace('src=\'/', 'src=\'http://www.allocine.fr/',$content);
            $content = str_replace('href=\'/', 'href=\'http://www.allocine.fr/',$content);

            $a = $element->find("div.content h2 a", 0);
            
            $date = $element->find("div.content span.lighten", 0);
            
            $date = explode("-" , $date->innertext);
            
            $list = explode(" ", trim($date[0]));
            $jour = $list[1];
            $mois = 1;
            $annee = $list[3];

            switch (strtolower($list[2]))
            {
                case "janvier" :
                    $mois = 1;
                    break;
                case "février" :
                case "fevrier" :
                    $mois = 2;
                    break;
                case "mars" :
                    $mois = 3;
                    break;
                case "avril" :
                    $mois = 4;
                    break;
                case "mai" :
                    $mois = 5;
                    break;
                case "juin" :
                    $mois = 6;
                    break;
                case "juillet" :
                    $mois = 7;
                    break;
                case "aout" :
                case "août" :
                    $mois = 8;
                    break;
                case "septembre" :
                    $mois = 9;
                    break;
                case "octobre" :
                    $mois = 10;
                    break;
                case "novembre" :
                    $mois = 11;
                    break;
                case "decembre" :
                case "décembre" :
                    $mois = 12;
                    break;
            }
            
            $item->content = $content;
            $item->title = trim($a->innertext);
            $item->uri = "http://www.allocine.fr" . $a->href;
            $item->timestamp = mktime(0, 0, 0, $mois, $jour, $annee);

            $this->items[] = $item;
        }
    }

    public function getName(){
        return 'Allo Cine : ' . $this->_NOM;
    }

    public function getURI(){
        return $this->_URL;
    }

    public function getCacheDuration(){
        return 25000; // 7 hours
    }
    public function getDescription(){
        return "Allo Cine : " . $this->_NOM . " via rss-bridge";
    }
}
?>
