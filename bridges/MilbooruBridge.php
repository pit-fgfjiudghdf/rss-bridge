<?php
/**
* RssBridgeMilbooru
* Returns images from given page
*
* @name Milbooru
* @description Returns images from given page
* @use1(p="page",t="tags")
*/
class MilbooruBridge extends BridgeAbstract{

    public function collectData(array $param){
	$page = 0;$tags='';
        if (isset($param['p'])) { 
            $page = (int)preg_replace("/[^0-9]/",'', $param['p']); 
        }
        if (isset($param['t'])) { 
            $tags = urlencode($param['t']); 
        }
        $html = file_get_html("http://sheslostcontrol.net/moe/shimmie/index.php?q=/post/list/$tags/$page") or $this->returnError('Could not request Milbooru.', 404);


	foreach($html->find('div[class=shm-image-list] span[class=thumb]') as $element) {
		$item = new \Item();
		$item->uri = 'http://sheslostcontrol.net/moe/shimmie/'.$element->find('a', 0)->href;
		$item->postid = (int)preg_replace("/[^0-9]/",'', $element->find('a', 0)->getAttribute('data-post-id'));	
		$item->timestamp = time();
		$item->thumbnailUri = 'http://sheslostcontrol.net/moe/shimmie/'.$element->find('img', 0)->src;
		$item->tags = $element->find('a', 0)->getAttribute('data-tags');
		$item->title = 'Milbooru | '.$item->postid;
		$item->content = '<a href="' . $item->uri . '"><img src="' . $item->thumbnailUri . '" /></a><br>Tags: '.$item->tags;
		$this->items[] = $item; 
	}
    }

    public function getName(){
        return 'Milbooru';
    }

    public function getURI(){
        return 'http://sheslostcontrol.net/moe/shimmie/';
    }

    public function getCacheDuration(){
        return 1800; // 30 minutes
    }
}
