<?php
// email,name
if(empty( $_POST['download_email']))
{
    echo json_encode(['status' => false, 'message' => 'Empty email']);
    die;
}
if(empty( $_POST["download_name"]))
{
    echo json_encode(["status" => false, "message" => "Empty name"]);
    die;
}

// Read the form values
// email name
$success = false;
$senderName = isset( $_POST['download_name'] ) ? preg_replace( "/[^\.\-\' a-zA-Z0-9]/", "", $_POST['download_name'] ) : "";
$senderEmail = isset( $_POST['download_email'] ) ? preg_replace( "/[^\.\-\_\@a-zA-Z0-9]/", "", $_POST['download_email'] ) : "";

// If all values exist, send the email
if ( $senderName && $senderEmail ) {
    // Build POST request:
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6LeK4RsmAAAAAM0GwUMbrvtPA9dz5SEund9F9j64';
    $recaptcha_response = $_POST['g-recaptcha-response'];

	if (!$recaptcha_response){
		echo json_encode(['status' => 'error', 'message' => 'There was a problem sending your message. Captcha not found. Please try again.']);
		die();
	}
	
    $post = 'secret='.$recaptcha_secret.'&response='.$recaptcha_response;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $recaptcha_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ($post));
    $result = curl_exec($ch);
    $recaptcha = json_decode($result, true);

    // Take action based on the score returned:
    if(!$recaptcha["success"])
    {
		echo json_encode(['status' => 'error', 'message' => 'There was a problem sending your message. Captcha incorrect. Please try again.']);
		die();
    }

	$nuren_sso_url = "https://sendy.incitefood.com/external-api.php";

	$post_param = [
		'email' => $senderEmail,
		'name' => $senderName,
        'type' => 'web_lead_download'
	];

    $post_data = http_build_query($post_param, '', '&');

	$ch_nuren = curl_init();
    curl_setopt($ch_nuren, CURLOPT_URL, $nuren_sso_url);
    curl_setopt($ch_nuren, CURLOPT_POST, 1);
    curl_setopt($ch_nuren, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_nuren, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch_nuren, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch_nuren, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch_nuren, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch_nuren, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded'));
    curl_setopt($ch_nuren, CURLOPT_POSTFIELDS, ($post_data));
    $result_nuren = curl_exec($ch_nuren);
	$json_return = json_decode($result_nuren, true);

	if($json_return['status'] == true)
	{
        //download
        echo json_encode(['status' => 'success', 'message' => 'succesfully submit', 'download_url' => 'https://www.inciteinnovationsinc.com/company-profile.pdf']);
        die();
		
	}else 
	{
		echo json_encode(['status' => 'error', 'message' => $json_return['message']]);
		die();
	}
}


?>