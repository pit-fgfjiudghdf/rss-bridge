<?php
/**
* RssBridgeGizmodo
* Returns the 10 newest posts from http://www.gizmodo.fr (full text)
*
* @name Gizmodo
* @description Returns the 10 newest posts from Gizmodo (full text)
*/
class GizmodoBridge extends BridgeAbstract{

    public function collectData(array $param){

    function GizmodoStripCDATA($string) {
        $string = str_replace('<![CDATA[', '', $string);
        $string = str_replace(']]>', '', $string);
        return $string;
    }
    function GizmodoExtractContent($url) {
        $html2 = file_get_html($url);
        $text = $html2->find('div[itemprop=description]', 0)->innertext;
        return $text;
    }
        $html = file_get_html('http://www.gizmodo.fr/feed') or $this->returnError('Could not request Gizmodo.', 404);
        $limit = 0;

        foreach($html->find('item') as $element) {
         if($limit < 10) {
         $item = new \Item();
         $item->title = GizmodoStripCDATA($element->find('title', 0)->innertext);
         $item->uri = GizmodoStripCDATA($element->find('guid', 0)->plaintext);
         $item->timestamp = strtotime($element->find('pubDate', 0)->plaintext);
         $item->content = GizmodoExtractContent($item->uri);
         $this->items[] = $item;
         $limit++;
         }
        }

    }

    public function getName(){
        return 'Gizmodo';
    }

    public function getURI(){
        return 'http://www.gizmodo.fr/';
    }

    public function getCacheDuration(){
        return 3600; // 1 hour
    }
}
