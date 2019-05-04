<?php
ini_set('max_execution_time', 300);


require("vendor/autoload.php");

$instagram = new \Instagram\Instagram();

function GetMediaId($url) {
 
    $user = curl_init("https://api.instagram.com/oembed?callback=&url=" . $url);
        
    curl_setopt($user, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($user, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($user, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($user, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($user, CURLOPT_HTTPHEADER, array(
        'x-ig-capabilities' =>'3w==',
        'user-agent'=> 'Instagram 9.5.1 (iPhone9,2; iOS 10_0_2; en_US; en-US; scale=2.61; 1080x1920) AppleWebKit/420+',
  
        
    ));
    
    $users = curl_exec($user);
    curl_close($user);
    $data = json_decode($users, true);

    return $data["media_id"];
}
function GetUserId($url)
{
    $username = parse_url($url, PHP_URL_PATH);
    $username = str_replace('/', '',$username);
   

    $user = curl_init("https://www.instagram.com/web/search/topsearch/?query=" . $username);
        
    curl_setopt($user, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($user, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($user, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($user, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($user, CURLOPT_HTTPHEADER, array(
        'x-ig-capabilities' =>'3w==',
        'user-agent'=> 'Instagram 9.5.1 (iPhone9,2; iOS 10_0_2; en_US; en-US; scale=2.61; 1080x1920) AppleWebKit/420+',
  
        
    ));
    
    $users = curl_exec($user);
    curl_close($user);
    $data = json_decode($users, true);

    return $data["users"][0]["user"]["pk"];
}

//if(!$_POST){
//    echo "error";
 //   die();  
//}

$accounts = "accounts.txt";
$accounts = file($accounts);//file in to an array


foreach($accounts as $line) 
{
    $var = explode(' ', $line, 3);
    $users[] = [
        "username" => $var[0],
        "email" => $var[1],
        "password" => $var[2],
    ];
}

if((string)$_POST["type"] === "likes"){
    echo "Success 1";
    $i = 1;
    foreach($users as $k => $v) if ($tmp++ <= $_POST["num-likes"])
    {
        $instagram->login($v["username"], $v["password"]);
        $media = $_POST["post-url"]; //Input your url
        

        $instagram->likeMedia(GetMediaId($media));

        echo GetUserId($media);
        $instagram->logout();
        if ($i++ == $_POST["num-foll"]) break;
    }
}


if((string)$_POST["type"] === "followers"){
    echo "Success 2";
    $i = 1;
    foreach($users as $k => $v)
    {
        $instagram->login($v["username"], $v["password"]);
        $user = $_POST["pro-url"]; //Input your url
        
        $instagram->followUser(GetUserId($user));
        echo GetUserId($user);

        $instagram->logout();
        if ($i++ == $_POST["num-foll"]) break;
    }
}
