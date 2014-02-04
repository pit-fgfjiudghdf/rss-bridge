<?php
/**
* RssBridgeSeCoucherMoinsBete
* Returns the newest anecdotes
*
* @name Se Coucher Moins Bête
* @description Se Coucher Moins Bête via rss-bridge
* @author supitalp
* @update 17/10/2013 by Superbaillot : ajout de getDescription
*/
class ScmbBridge extends BridgeAbstract{
    
    public function collectData(array $param){
        $html = '';
        $html = file_get_html('http://secouchermoinsbete.fr/') or $this->returnError('Could not request Se Coucher Moins Bete.', 404);
        $cpt = 0;
        $cptMax = 7;
        foreach($html->find('article') as $article) {
            $item = new Item();
            // get publication date
            $str_date = $article->find('time',0)->datetime;
            
            //les news ne seront plus prise en compte. Le test n'est pas super explicite, mais ça marche
            if(strlen($str_date) >= 16)
            {
                $item->uri = 'http://secouchermoinsbete.fr'.$article->find('p.summary a',0)->href;
                $item->title = $article->find('header h1 a',0)->innertext;

                list($date, $time) = explode(' ', $str_date);
                list($y, $m, $d) = explode('-', $date);
                list($h, $i) = explode(':', $time);
                $timestamp = mktime($h,$i,0,$m,$d,$y);
                $item->timestamp = $timestamp;
                
                $adresse = $article->find('address span',0);
                $temp = $adresse->find("a", 0);
                if(!is_null($temp))
                {
                    $adresse = $temp;
                }
                $item->name = $adresse->innertext;

                // TODO: this should be optional since it is highly time and broadband consuming
                // check if the anecdote has more content to offer (text details, picture, video) and follow link to retrieve it if that is the case
                $optcontent = $article->find('div.metadata-list a');
                $hasPic = (preg_match("#pas#", $optcontent[0]->innertext)) ? false : true;
                $hasVid = (preg_match("#pas#", $optcontent[1]->innertext)) ? false : true;
                $hasDetails = (preg_match("#pas#", $optcontent[2]->innertext)) ? false : true;

                $article->find('span.read-more',0)->outertext=''; // remove text "En savoir plus" from anecdote content
                $content = $article->find('p.summary a',0)->innertext;
                $content = substr($content,0,strlen($content)-17); // remove superfluous spaces at the end
                
                if($hasDetails || $hasPic || $hasVid){
                    $cpt++;
                    if($cpt<$cptMax)
                    {
                        $opt_html = file_get_html($item->uri);
                    }
                }
                if($hasDetails && $cpt<$cptMax){
                    $details = $opt_html->find('p.details',0)->innertext;
                    $content = $content . '<br />' . $details;
                }
                if($hasPic && $cpt<$cptMax){
                    $picUri = $opt_html->find('div#sources-image-wrapper a',0)->href;
                    $item->pictureUri = $picUri;
                    $content = $content . '<br /><img src="' . $item->pictureUri . '" />';
                }
                if($hasVid && $cpt<$cptMax){
                    $vidUri = $opt_html->find('div#sources-video-wrapper iframe',0)->src;
                    $vidUri = explode('?', $vidUri); // remove "?autoplay=0"
                    $item->vidUri = $vidUri[0];
                    $content = $content . '<br /><a href="' . $vidUri[0] . '">Vidéo</a>';
                }

                $item->content = $content;
                $this->items[] = $item;
            }
        }
    }

    public function getName(){
        return 'Se Coucher Moins Bête Bridge';
    }

    public function getURI(){
        return 'http://secouchermoinsbete.fr/';
    }

    public function getDescription(){
        return 'Se Coucher Moins Bête Bridge via rss-bridge';
    }

    public function getCacheDuration(){
        return 21600; // 6 hours
    }
}
?>
