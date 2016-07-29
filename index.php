<?php
ini_set("log_errors", 1);
ini_set("error_log", __DIR__."/error_log");

require 'farsiGD.php';
$gd = new FarsiGD();
ob_start();
define('API_KEY','XXX:XXX');// your API KEY here ...
$update = json_decode(file_get_contents('php://input'));
function makeHTTPRequest($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}

if(isset($update->message)){
    $text = $update->message->text;
    if($text == "/start" || $text == '/help'){
        makeHTTPRequest('sendMessage',[
            'chat_id'=>$update->message->chat->id,
            'text'=>'<b>Welcome to TEXT TO STICKER bot</b>

▪️This robot convert your messages into a sticker

▪️be careful with your message to avoid overflow.

▪️now send me a <b>message</b> and i will convert it :)

▪️also you can <a href="https://telegram.me/pp2007ws">Contact Me !</a>',
            'parse_mode'=>'HTML',
            'disable_web_page_preview'=>true
        ]);
        return false;
    }

    /**
     * Making the Sticker
     */


    $exploded_text = explode("\n",$text);
    foreach($exploded_text as $similer_text){
        if(mb_strlen($similer_text) > 50 ){
            echo 'MAX 20 CHARACTERS';
            return false;
        }
    }
//
//$count_lines = count($exploded_text);
//$resource_image = imagecreatetruecolor(512,512);
//$bgImage = imagecolorallocate($resource_image,236, 240, 241);
//imagefilledrectangle($resource_image,0,0,512,512,$bgImage);
//imagettftext($resource_image,14,0,10,10,imagecolorallocate($resource_image,0,0,0),__DIR__.'/font.ttf',
//    fagd($text,'fa','nastaligh')
//    );
//
//imagepng($resource_image,'log.png');
//

    $image = new Imagick('texttotsticker.png');


    $draw = new ImagickDraw();
    $draw->setFillColor('white');
    $draw->setFont('font.ttf');
    $draw->setFontSize(35);
    $draw->setTextEncoding('utf-8');
    $draw->setTextInterLineSpacing(30);
    $draw->setGravity(Imagick::GRAVITY_CENTER);
    preg_match('/([a-zA-Z]*)/',$text,$matches,PREG_OFFSET_CAPTURE);
    var_dump($matches);
    if(!isset($matches[0][0]) || $matches[0][0] == ""){
        $image->annotateImage($draw,0,0,0,str_replace("{DIF}","\n",$gd->persianText((implode("{DIF}",array_reverse($exploded_text))),'fa','normal')));
    }else{
        $image->annotateImage($draw,0,0,0,$text);
    }
    //$image->annotateImage($draw,0,0,0,str_replace("*********#**********","\n",$gd->persianText((implode("*********#**********",array_reverse($exploded_text))),'fa','normal')));
    $image->setImageFormat('png');
    $theSticker = $image;

    $tmpFile = tmpfile();
    fwrite($tmpFile,$theSticker);
    fseek($tmpFile,0);

    $meta = stream_get_meta_data($tmpFile);
    $filePath = $meta['uri'];


    /**
     * end Making the Sticker
     */

    echo 'the PATH :';
    echo $filePath;
    echo "\n";

    var_dump(makeHTTPRequest('sendSticker',[
       'chat_id'=>$update->message->chat->id,
        'sticker'=> new CURLFile($filePath)
    ]));



}



file_put_contents('the_log',ob_get_clean());
