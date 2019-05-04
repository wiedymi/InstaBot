<?php
ini_set('max_execution_time', 300);
error_reporting(E_ALL);
ini_set('display_errors', 0);

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

if(!$_GET){
   echo "error";
   die();  
}

$accounts = "accounts.txt";
$accounts = file($accounts);//file in to an array


foreach($accounts as $line) 
{
    $var = explode(' ', $line, 3);
    $users[] = [
        "username" => $var[0],
        "email" => $var[1],
        "password" => trim($var[2]),
    ];
}

if($_GET["type"] === "likes"){
    $returns = [];
   
    $i = 1;
    foreach($users as $k => $v)
    {
        try {
        $instagram->login($v["username"], $v["password"]);
        $media = $_GET["post-url"]; //Input your url
        

        $instagram->likeMedia(GetMediaId($media));

       
        $returns[] = [
            "status" => "success",
            "username" => $v["username"],
            "email" => $v["email"],
        ];
        $instagram->logout();
      
        } catch(Exception $e){
            $returns[] = [
                "status" => "error",
                "message" => $e->getMessage(),
                "email" => $v["email"],
                "username" => $v["username"],
                "password" => $v["password"]
            ];
        }
        if ($i++ == $_GET["num-likes"]) break;
    }
   
    
}


if($_GET["type"] === "followers"){
    $returns = [];
    
    (int)$i = 1;
    foreach($users as $k => $v)
    {
        try {
        $instagram->login($v["username"], $v["password"]);
        $user = $_GET["pro-url"]; //Input your url
        
        $instagram->followUser(GetUserId($user));
        
        $returns[] = [
            "status" => "success",
            "username" => $v["username"],
            "email" => $v["email"],
        ];
        $instagram->logout();
      
        } catch(Exception $e){
            $returns[] = [
                "status" => "failed",
                "message" => $e->getMessage(),
                "email" => $v["email"],
                "username" => $v["username"],
                "password" => $v["password"]
            ];
        }
        if ($i++ == $_GET["num-foll"]) break;
    }
  
    
}
$returnsSuccess = array_search('success', array_column($returns, 'status'));
$returnsFails = array_search('failed', array_column($returns, 'status'));
var_dump($returns);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link rel="stylesheet" href="css/app.css">
    <script src="js/jquery.min.js"></script>
    <title>InstaBot</title>
</head>
<body>
    <header>
        <div>
            <h1>InstaBot - Results</h1>
        </div>
    </header>
    <div class="container">
        <div class="grid-12">
            <h2>SUCCESS: <?php echo count($returnsSuccess);?></h2>
        </div>
        <div class="grid-12">
            <h2>FAIlS: <?php echo count($returnsFails);?></h2>
            <?php foreach($returns as $k): ?>
            <div class="grid-12">
                <p>User: <?php echo $k["username"]; ?></p>
                <p>Message:</p>
                <p>
                <?php echo $k["message"]; ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>