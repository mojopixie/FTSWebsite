<?php
// require ReCaptcha class
require('/home/finetechnology/fineonline.com/assets/recaptcha/recaptcha-master/src/autoload.php');

// configure
// an email address that will be in the From field of the email.
$from = 'mfine@fineonline.com';

// an email address that will receive the email with the output of the form
$sendTo = 'mfine@fineonline.com';

// subject of the email
$subject = "New message from fineOnlione.com's contact form";

// form field names and their translations.
// array variable name => Text to appear in the email
$fields = array('name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'message' => 'Message');

// message that will be displayed when everything is OK :)
$okMessage = 'Message submitted.<br> Thank you! We will contact you shortly!';

// If something goes wrong, we will display this message.
$errorMessage = 'Form Submission Error.<br> Please try again.';

// ReCaptcha Secret
$recaptchaSecret = '6LcmAaYUAAAAANKWx5uat27E8U6iAV-8H_Dkf2Bo';

// let's do the sending



// if you are not debugging and don't need error reporting, turn this off by error_reporting(0);
error_reporting(E_ALL & ~E_NOTICE);

try {
    if (!empty($_POST)) {

        // validate the ReCaptcha, if something is wrong, we throw an Exception,
        // i.e. code stops executing and goes to catch() block
        
        if (!isset($_POST['g-recaptcha-response'])) {
            throw new \Exception('ReCaptcha is not set.');
        }

        // do not forget to enter your secret key from https://www.google.com/recaptcha/admin
        
        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret, new \ReCaptcha\RequestMethod\CurlPost());
        
        // we validate the ReCaptcha field together with the user's IP address        
        $response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if (!$response->isSuccess()) {
            throw new \Exception('ReCaptcha was not validated.');
        }
        
        // everything went well, we can compose the message, as usual        
        $emailText = "You have a new message from your website's contact form\n================================================\n";

        foreach ($_POST as $key => $value) {
            // If the field exists in the $fields array, include it in the email
            if (isset($fields[$key])) {
                $emailText .= "$fields[$key]: $value\n";
            }
        }
    
        // All the neccessary headers for the email.
        $headers = array('Content-Type: text/plain; charset="UTF-8";',
            'From: ' . $from,
            'Reply-To: ' . $from,
            'Return-Path: ' . $from,
        );
        
        // Send email
        mail($sendTo, $subject, $emailText, implode("\n", $headers));

        $responseArray = array('type' => 'success', 'message' => $okMessage);
    }
} catch (\Exception $e) {
    $responseArray = array('type' => 'danger', 'message' => $e->getMessage());
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);

    header('Content-Type: application/json');

    echo $encoded;
} else {
    echo $responseArray['message'];
}
