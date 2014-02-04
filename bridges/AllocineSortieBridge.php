<?php
/**
*
* @name Allo Cine : sorties de la semaine
* @description Allo Cine : sorties de la semaine via rss-bridge
* @update 05/12/2013
*/
class AllocineSortieBridge extends BridgeAbstract{

    public function collectData(array $param){
        $html = file_get_html('http://www.allocine.fr/film/cettesemaine.html') or $this->returnError('Could not request Allo cine.', 404);

        $pageAAnalyser = true;
        $cpt = 59;    //pour les classer comme sur le site qui est un ordre, certe subjectif, mais qui est à peu près correct en fonction de l'attente des films
        
        $listeUrl = array();
    
        $nav = $html->find('div.navbar', 0);
        $ul = $nav->find('ul', 0);

        //je recherche combien de page il y a
        foreach ($ul->find("li") as $li)
        {
            if($li != null && count($li->find("a")) > 0)
            {
                $an = $li->find("a", 0);
                $listeUrl[] = "http://www.allocine.fr" . $an->href;
            }
        }
        rsort($listeUrl);
        
        while($pageAAnalyser)
        {
            foreach($html->find('div.data_box') as $element)
            {
                $item = new Item();
                $main = $element->find('div.content', 0);
                $titre = $main->find('div.titlebar_02', 0);
                $a = $titre->find('a', 0);
                
                $contenzone = $main->find('table', 0);
                $posDate = strpos($contenzone->innertext, "Date de sortie");
                
                if($posDate !== false)
                {
                    $d = $contenzone->find("div", 0);
                    $timestamp = 0;
                    if(count($d->find("span")) > 0)
                    {
                        $span = $d->find("span", 0);
                        $timestamp = (strtotime($span->content) + $cpt);
                    }
                    $content = str_replace('src="/', 'src="http://www.allocine.fr/',trim($element->innertext));
                    $content = str_replace('href="/', 'href="http://www.allocine.fr/',$content);
                    $content = str_replace('src=\'/', 'src=\'http://www.allocine.fr/',$content);
                    $content = str_replace('href=\'/', 'href=\'http://www.allocine.fr/',$content);
                    
                    $item->content = $content;
                
                    $item->timestamp = $timestamp;
                    $item->title = $a->innertext;
                    $item->uri = "http://www.allocine.fr" . $a->href;
                    $this->items[] = $item;
                    $cpt--;
                }
            }
            if(count($listeUrl) > 0)
            {
                $url = array_pop($listeUrl);
                $html = file_get_html($url) or $this->returnError('Could not request Allo cine.', 404);
            }
            else
            {
                $pageAAnalyser = false;
            }
        }
    }

    public function getName(){
        return 'Allo Cine : sorties de la semaine';
    }

    public function getURI(){
        return 'http://www.allocine.fr/film/cettesemaine.html';
    }

    public function getCacheDuration(){
        return 25200; // 7 hours
    }
    public function getDescription(){
        return "Allo Cine : sorties de la semaine via rss-bridge";
    }
}
?>
