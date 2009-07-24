<?php

/**
 * sfPaymentPayPal Class
 *
 * This provides support for PayPal to sfPaymentPlugin. It has 
 * been inspired from Md Emran Hasan work (http://www.phpfour.com).
 *
 * @package   sfPaymentPayPal
 * @category  Library
 * @author    Md Emran Hasan <phpfour@gmail.com>
 * @author    Johnny Lattouf <johnny.lattouf@letscod.com>
 * @author    Antoine Leclercq <antoine.leclercq@letscod.com>
 * @link      http://www.phpfour.com
 * @link      http://wiki.github.com/letscod/sfPaymentPlugin
 */

class sfPaymentPayPal extends sfPaymentGatewayInterface {
	
	public function __construct() {
		parent::__construct();
		
		// translation table
		$this->addFieldTranslation('Vendor',          'business');
		$this->addFieldTranslation('Currency',        'currency_code');
    $this->addFieldTranslation('Amount',          'amount');
    $this->addFieldTranslation('ProductName',     'item_name');
    $this->addFieldTranslation('ProductPrice',    'item_price');
    // specify the url where paypal will send the user on success/failure
    $this->addFieldTranslation('Return',          'return');
    // specify the url where paypal will send the user on cancel
    $this->addFieldTranslation('CancelReturn',    'cancel_return');
    // specify the url where paypal will send the IPN
    $this->addFieldTranslation('Notify',          'notify_url');
    $this->addFieldTranslation('Rm',              'rm');
    $this->addFieldTranslation('Cmd',             'cmd');

    // default values of the class
		$this->gatewayUrl = 'https://www.paypal.com/cgi-bin/webscr';
		$this->ipnLogFile = 'paypal.ipn_results.log';

		// populate $fields array with a few default
		$this->setRm('2');           // return method = POST
		$this->setCmd('_xclick');
		
		// set from config values
		$this->setReturn(url_for(sfConfig::get('app_sf_payment_paypal_plugin_return','sfPaymentPayPal/success'),true));
		$this->setCancelReturn(url_for(sfConfig::get('app_sf_payment_paypal_plugin_cancel_return','sfPaymentPayPal/failure'),true));
		$this->setNotify(url_for(sfConfig::get('app_sf_payment_paypal_plugin_notify','sfPaymentPayPal/ipn'),true));

		if(sfConfig::get('app_sf_payment_paypal_plugin_business'))
		  $this->setVendor(sfConfig::get('app_sf_payment_paypal_plugin_business'));
		else
		  throw new sfException('No business paypal acccount referenced in app.yml.<br />Please check the README file.');
	}
	
	/**
	 * Enables test mode
	 *
	 * @param none
	 * @return none
	 */
	public function enableTestMode()
  {
  	$this->testMode = true;
    $this->gatewayUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    
    $test = sfConfig::get('app_sf_payment_paypal_plugin_test');
    
    if(isset($test['business']))
      $this->setVendor($test['business']);
    else
      throw new sfException('No test business paypal acccount referenced in app.yml.<br />Please check the README file.');
  }
    
 	/**
	 * Validate the IPN notification
	 *
	 * @param none
	 * @return boolean
	 */
	public function validateIpn($parameters = array())
	{
		// retrieve the parameters
		$this->ipnData = $parameters;
		$parameters["cmd"] = "_notify-validate";
		$browser = new sfWebBrowser(array("Content-type: application/x-www-form-urlencoded\r\n","Connection: close\r\n\r\n"));
		$browser->post($this->gatewayUrl, $parameters);
		
		$this->ipnResponse= $browser->getResponseText();
		
		if($browser->getResponseText() === "VERIFIED")
    {
      // Valid IPN transaction.
      $this->logResults(true);
      return true;
    }
    else
    {
    	// Invalid IPN transaction.  Check the log for details.
      $this->lastError = "IPN Validation Failed . ".$this->gatewayUrl;
      $this->logResults(false);
      return false;
    }
	}
	
	/**
	 * Check if the payment status is completed after ipn validation 
	 *
	 * @return boolean
	 */
	public function isCompleted() {
		if($this->ipnData['payment_status'] == 'Completed')
		  return true;
		else
		  return false;
	}
}