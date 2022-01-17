<?php
/**
    @author Izzeldin Addarda
    @github github/zelsaddr/BerisiCloneBlog
*/
class berisiAPI {
    public $berita = [];

    public function __construct($page = 1, $search = false){
        if($search){
            $this->getBerisiDetailsBySearch($search);
        }else{
            if($page == 1){
                $this->getBerisiDetails();
            }else{
                $this->getBerisiDetailsByPage($page);
            }
        }
    }

    public function read_post($base64url, $title, $author, $post_date){
        $ori_url = base64_decode(str_rot13($base64url));
        if(preg_match("/berisi/i", $ori_url)) {
            $content = $this->cURL($ori_url);
            preg_match('#<article class="post-details">(.*?)<div class="quads-location quads-ad1"#si', $content[1], $c);
            preg_match_all('#<p.*>(.*?)<\/p>#si', $c[1], $p);
            preg_match('#axil-post-has-image" style="background-image: url\((.*?)\)#si', $content[1], $img);
            preg_match_all('#<div class=\"media-caption grad-overlay\">(.*?\n.*?)<\/a><\/div>#si', $content[1], $artikel_terkait);
            preg_match('#<div class="post-title-wrapper">(.*?)<\/ul><\/div>#si', $content[1], $cat);
            preg_match_all('#href=".*\/category\/(.*?)"#si', $cat[0], $category);
            $post_terkait = [];
            foreach($artikel_terkait[0] as $ar){
                preg_match('#<h3 class="axil-post-title hover-line"><a href="(.*?)">(.*?)<\/a><\/h3><div class="caption-meta">\n.*author">(.*?)<\/a>#si', $ar, $detil);
                $post_terkait[] = array(
                    'title_berita' => $detil[2],
                    'url_berita'   => $detil[1],
                    'author'       => $detil[3],
                    'post_date'    => '-',
                );
            }
            $this->berita['now_reading'] = array(
                'title_berita'  => $title,
                'author'        => $author,
                'post_date'     => $post_date,
                'element'       => $c[1],
                'img_src'       => $img[1],
                'post_terkait'  => $post_terkait,
                'category'      => str_replace("/", "", $category[1][0])
            );
        }else{
            return false;
        }
    }
    
    private function getPagination($html_content){
        preg_match('#<div class="axil-post-pagination">(.*?)<\/ul><\/div>#si', $html_content, $c);
        preg_match('#<div class="axil-post-pagination">(.*?)<\/div>#si', $c[0], $ul);
        $rep = preg_replace('#https\:\/\/berisi\.id\/artikel\/page\/#si', './?page=', $ul[1]);
        $rep = preg_replace('#https\:\/\/berisi\.id\/artikel\/#si', './?page=1', $rep);
        $rep = preg_replace('#class="axil-post-pagination-holder"#si', 'class="pagination justify-content-start"', $rep);
        $rep = preg_replace('#<li>#si', '<li class="page-item">', $rep);
        $rep = preg_replace('#<a href="#si', '<a class="page-link" href="', $rep);
        $this->berita['pagination'] = $rep;
    }

    private function getBerisiDetails(){
        $recent = [];
        $content = $this->cURL("https://berisi.id/artikel/");
        preg_match_all("#<div class=\"col-lg-12\">(.*?)<\/p>#si", $content[1], $recent_news);
        foreach($recent_news[0] as $r){
            preg_match('#<h3 class=\"axil-post-title hover-line\"><a href=\"(.*?)\".*bookmark\">(.*?)<\/a>#si', $r, $judul);
            preg_match('#<img alt=".*" data-src="(.*?)"#si', $r, $img);
            preg_match('#<span class="vcard author author_name"><span class="fn">(.*?)<\/span>#si', $r, $author);
            preg_match('#<i class=\"dot\">.<\/i>(.*?)<\/li>#si', $r, $post_date);
            preg_match('#<p>(.*?)<\/p>#si', $r, $desc);
            $recent[] = array(
                'title_berita'      => $judul[2],
                'url_berita'        => $judul[1],
                'img_src'           => $img[1],
                'author'            => $author[1],
                'post_date'         => $post_date[1],
                'desc'              => $desc[1]
            );
        }
        $this->berita['recent_news'] = $recent;
        $this->getPagination($content[1]);
    }

    private function getBerisiDetailsByPage($page){
        $recent = [];
        $content = $this->cURL("https://berisi.id/artikel/page/{$page}");
        preg_match_all("#<div class=\"col-lg-12\">(.*?)<\/p>#si", $content[1], $recent_news);
        foreach($recent_news[0] as $r){
            preg_match('#<h3 class=\"axil-post-title hover-line\"><a href=\"(.*?)\".*bookmark\">(.*?)<\/a>#si', $r, $judul);
            preg_match('#<img alt=".*" data-src="(.*?)"#si', $r, $img);
            preg_match('#<span class="vcard author author_name"><span class="fn">(.*?)<\/span>#si', $r, $author);
            preg_match('#<i class=\"dot\">.<\/i>(.*?)<\/li>#si', $r, $post_date);
            preg_match('#<p>(.*?)<\/p>#si', $r, $desc);
            $recent[] = array(
                'title_berita'      => $judul[2],
                'url_berita'        => $judul[1],
                'img_src'           => $img[1],
                'author'            => $author[1],
                'post_date'         => $post_date[1],
                'desc'              => $desc[1]
            );
        }
        $this->berita['recent_news'] = $recent;
        $this->getPagination($content[1]);
    }
    
    private function getBerisiDetailsBySearch($search){
        $recent = [];
        $content = $this->cURL("https://berisi.id/?s={$search}");
        preg_match_all("#<div class=\"col-lg-12\">(.*?)<\/p>#si", $content[1], $recent_news);
        foreach($recent_news[0] as $r){
            preg_match('#<h3 class=\"axil-post-title hover-line\"><a href=\"(.*?)\".*bookmark\">(.*?)<\/a>#si', $r, $judul);
            preg_match('#<img alt=".*" data-src="(.*?)"#si', $r, $img);
            preg_match('#<span class="vcard author author_name"><span class="fn">(.*?)<\/span>#si', $r, $author);
            preg_match('#<i class=\"dot\">.<\/i>(.*?)<\/li>#si', $r, $post_date);
            preg_match('#<p>(.*?)<\/p>#si', $r, $desc);
            $recent[] = array(
                'title_berita'      => $judul[2],
                'url_berita'        => $judul[1],
                'img_src'           => $img[1],
                'author'            => $author[1],
                'post_date'         => $post_date[1],
                'desc'              => $desc[1]
            );
        }
        $this->berita['recent_news'] = $recent;
        $this->berita['pagination'] = '';
    }

    private function cURL ($url, $post = 0, $httpheader = 0, $proxy = 0, $uagent = 0){ // url, postdata, http headers, proxy, uagent
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        if($post){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if($httpheader){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        }else{
            $headers = array();
            $headers[] = 'Authority: berisi.id';
            $headers[] = 'Cache-Control: max-age=0';
            $headers[] = 'Sec-Ch-Ua: \" Not A;Brand\";v=\"99\", \"Chromium\";v=\"90\", \"Google Chrome\";v=\"90\"';
            $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
            $headers[] = 'Upgrade-Insecure-Requests: 1';
            $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36';
            $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
            $headers[] = 'Sec-Fetch-Site: none';
            $headers[] = 'Sec-Fetch-Mode: navigate';
            $headers[] = 'Sec-Fetch-User: ?1';
            $headers[] = 'Sec-Fetch-Dest: document';
            $headers[] = 'Accept-Language: en-US,en;q=0.9,id;q=0.8';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if($proxy){
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
            // curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        if($uagent){
            curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
        }else{
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:66.0) Gecko/20100101 Firefox/".rand(1,200).".0");
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch);
        if(!$httpcode) return "Curl Error : ".curl_error($ch); else{
            $header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
            $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
            curl_close($ch);
            return array($header, $body);
        }
    }
}

?>
