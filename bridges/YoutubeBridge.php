<?php
/**
* RssBridgeYoutube 
* Returns the newest videos
*
* @name Youtube Bridge
* @description Returns the newest videos by username or playlist
* @use1(u="username")
* @use1(p="playlist id")
*/
class YoutubeBridge extends BridgeAbstract{
    
    private $request;
    
    public function collectData(array $param){
        $html = '';
        if (isset($param['u'])) {   /* user timeline mode */
            $this->request = $param['u'];
            $html = file_get_html('https://www.youtube.com/user/'.urlencode($this->request).'/videos') or $this->returnError('Could not request Youtube.', 404);

	        foreach($html->find('li.channels-content-item') as $element) {
            	 $item = new \Item();
           	 $item->uri = 'https://www.youtube.com'.$element->find('a',0)->href;
           	 $item->thumbnailUri = 'https:'.$element->find('img',0)->src;
           	 $item->title = trim($element->find('h3',0)->plaintext);
           	 $item->content = '<a href="' . $item->uri . '"><img src="' . $item->thumbnailUri . '" /></a><br><a href="' . $item->uri . '">' . $item->title . '</a>';
           	 $this->items[] = $item;
        	}
        }
        else if (isset($param['p'])) {   /* playlist mode */
            $this->request = $param['p'];
            $html = file_get_html('https://www.youtube.com/playlist?list='.urlencode($this->request).'') or $this->returnError('Could not request Youtube.', 404);

        	foreach($html->find('li.playlist-video-item') as $element) {
           	 $item = new \Item();
           	 $item->uri = 'https://www.youtube.com'.$element->find('a',0)->href;
            	$item->thumbnailUri = 'https:'.$element->find('img',0)->src;
            	$item->title = trim($element->find('h3',0)->plaintext);
            	$item->content = '<a href="' . $item->uri . '"><img src="' . $item->thumbnailUri . '" /></a><br><a href="' . $item->uri . '">' . $item->title . '</a>';
            	$this->items[] = $item;
        	}
		$this->request = 'Playlist '.str_replace(' - YouTube', '', $html->find('title', 0)->plaintext).', by '.$html->find('h1', 0)->plaintext;
        }
        else {
		$this->returnError('You must either specify a Youtube username (?u=...) or a playlist id (?p=...)', 400);
	}
   
    }

    public function getName(){
        return (!empty($this->request) ? $this->request .' - ' : '') .'Youtube Bridge';
    }

    public function getURI(){
        return 'https://www.youtube.com/';
    }

    public function getCacheDuration(){
        return 10800; // 3 hours
    }
}
