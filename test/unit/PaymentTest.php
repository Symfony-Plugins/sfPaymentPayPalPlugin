<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';

// lime test
$t = new lime_test(8, new lime_output_color());

/**
 * Test config
 */
$t->comment('Loading test.yml');
$test_file = dirname(__FILE__).'/../../config/test.yml';
if(!is_file($test_file)) {
	$t->fail('no test.yml file, please check the README file');
  return; 
}
else 
  $t->pass("Configuration loaded");
// open the test.yml file
$file = fopen($test_file,"r");
$yaml = stream_get_contents($file);
// loading the data
$sf_payment_paypal_test = sfYaml::load($yaml);

// check developer email
if(!isset($sf_payment_paypal_test['developer_email'])) {
	$t->fail('no test developer email set in test.yml, please check the README file');
	return; 
}
// check developer password
if(!isset($sf_payment_paypal_test['developer_password'])) {
	$t->fail('no test developer password set in test.yml, please check the README file');
	return; 
}
// check buyer email
if(!isset($sf_payment_paypal_test['buyer_email'])) {
	$t->fail('no test buyer email set in test.yml, please check the README file');
	return; 
}
// check buyer password
if(!isset($sf_payment_paypal_test['buyer_password'])) {
	$t->fail('no test buyer password set in test.yml, please check the README file');
	return;  
}
// check application url
if(!isset($sf_payment_paypal_test['application_url']) || !$sf_payment_paypal_test['application_url']) {
	$t->fail('no test application url set in test.yml, please check the README file');
	return; 
}

// sfWebBrowser using sfCurlAdapter, cookies enabled for sandbox authentication
$web_browser = new sfWebBrowser(array(),"sfCurlAdapter", array('cookies' => true));

// delete the cookies if exist
if(is_file($file = sfConfig::get('sf_data_dir').'/sfWebBrowserPlugin/sfCurlAdapter/cookies.txt'))
  unlink($file);

/**
 * Login to sandbox
 */
$t->comment('sfPaymentPayPal/sample');
$web_browser->get($sf_payment_paypal_test['application_url'].'/sfPaymentPayPal/sample');
$t->comment('Pay with PayPal');
$web_browser->click('Pay with PayPal');
$t->comment('PayPal Sandbox');
$web_browser->click('PayPal Sandbox');
$t->comment('Login to sandbox');
$web_browser->setField('login_email', $sf_payment_paypal_test['developer_email'])
  ->setField('login_password', $sf_payment_paypal_test['developer_password'])
  ->click('Log In')
;

if(eregi("field_label_error", $web_browser->getResponseText()) || eregi('<span class="error">The email address or password you have entered does not match our records. Please try again.</span>', $web_browser->getResponseText())) {
  $t->fail('Could not login to paypal sandbox');
  return;
}
else
  $t->pass('Login successful');

/*
 * Go back to site
 */
$t->comment('sfPaymentPayPal/sample');
$web_browser->get($sf_payment_paypal_test['application_url'].'/sfPaymentPayPal/sample');
$t->comment('Pay with PayPal');
$web_browser->click('Pay with PayPal');

$t->comment('Go back to site');

// guessing the link and seller name
$t->comment('Guessing the link');
$ends_with = "'s Test Store";
$dom = $web_browser->getResponseDom();
$xpath = new DomXpath($dom);
if ($h1s = $xpath->query('//h1'))
{
  foreach($h1s as $h1)
  {
    $nodeValue = preg_replace(array('/\s{2,}/', '/\\r\\n|\\n|\\r/'), array(' ', ''), $h1->nodeValue);
    $nodeEndsWith = substr($nodeValue,strlen($nodeValue)-strlen($ends_with));
    if($nodeEndsWith == $ends_with)
    {
      $my_link = $nodeValue;
      $seller_name = str_replace($ends_with,"",$my_link);
      break;  
    }
  }
}

if(!isset($my_link))
  $t->fail('Could not guess the link and seller name');
else
  $t->pass("Seller's name = \"".$seller_name."\" and the link is \"".$my_link."\"");
    
$web_browser->click($my_link);
$t->pass('Back to '.$my_link.' successfully');
  
/*
 * Login to pay
 */
$t->comment('sfPaymentPayPal/sample');
$web_browser->get($sf_payment_paypal_test['application_url'].'/sfPaymentPayPal/sample');
$t->comment('Pay with PayPal');
$web_browser->click('Pay with PayPal');

$t->comment('Login to Paypal');
$web_browser->setField('login_email', $sf_payment_paypal_test['buyer_email'])
  ->setField('login_password', $sf_payment_paypal_test['buyer_password'])
  ->click('Log In');
  
if(eregi('class="messageBox error"', $web_browser->getResponseText())) {
  $t->fail('Login to Paypal failed');
  return;
}
else
  $t->pass('Login to Paypal successful');

/**
 * Pay
 */
$t->comment('Pay Now');
$web_browser->click('Pay Now');

$t->pass('Payment successful');

/**
 * Back to site
 */
$web_browser->click("Return to ".$my_link);
$t->pass('Return to '.$my_link);

/**
 * Checking payment status
 */
$t->comment('Checking payment status');
if(eregi("Payment Gateway Tests - Paypal Success", $web_browser->getResponseText()))
  $t->pass('Payment Gateway Tests - Paypal Success');
elseif(eregi("Payment Gateway Tests - Paypal verified but failed", $web_browser->getResponseText()))
  $t->fail('Payment Gateway Tests - Paypal verified but failed');
elseif(eregi("Payment Gateway Tests - Paypal Invalid", $web_browser->getResponseText()))
  $t->fail('Payment Gateway Tests - Paypal Invalid');
else
  $t->fail('Could not check the payment status');