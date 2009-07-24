<?php
abstract class BasesfPaymentPayPalActions extends sfActions
{
  public function executeSample(sfWebRequest $request)
  {
    // create paypal library instance
    $gateway = new sfPaymentPayPal();
    
    // instanciate transaction
    $this->transaction = new sfPaymentTransaction($gateway);
    
    // enable test mode if needed
    $this->transaction->enableTestMode();
    
    // define transaction information :
    
    // - currency
    $this->transaction->setCurrency("USD");
    
    // - product information
    $this->transaction->setAmount(rand(1, 100));
    $products_sample = array("Symfony Book", "View Sonic VA226w", "Camera", "IPhone 3Gs", "PlayStation 3");
    $this->transaction->setProductName($products_sample[rand(0, 4)]);
  }
  
  public function executeSuccess(sfWebRequest $request) {
    $this->getResponse()->setTitle(sfConfig::get('app_sf_payment_paypal_plugin_success_title', "Payment Gateway Tests - Paypal Success"), false);
    
    // possible check of verify_sign first but paypal lacks documentation on this part
    // it is not mandatory, but saves a query to paypal is payment data is not verified locally
    
    // verify using ipn
    $this->executeIpn($request);
  }
  
  public function executeFailure(sfWebRequest $request) {
    $this->getResponse()->setTitle(sfConfig::get('app_sf_payment_paypal_plugin_failure_title', "Payment Gateway Tests - Paypal Failure"), false);
    
    $this->transactionCanceled($request);
  }
  
  public function executeIpn(sfWebRequest $request) {
    
    // create paypal library instance
    $gateway = new sfPaymentPayPal();
    
    // instanciate transaction
    $this->transaction = new sfPaymentTransaction($gateway);
    
    // enable test mode if needed
    $this->transaction->enableTestMode();
    
    // check validity and write down it
    if ($this->transaction->validateIpn($request->getPostParameters()))
    {
      if($this->transaction->isCompleted())
      {
      	$this->setTemplate("completed");
        $this->transactionCompleted($request);
      }
      else
      {
      	$this->setTemplate("failed");
        $this->transactionFailed($request);
      }
    }
    else
    {
    	$this->setTemplate("invalid");
      $this->transactionInvalid($request);
    }
  }
  
  /**
   * Transaction verified and completed
   *
   * @param array $post_parameters
   */
  abstract public function transactionCompleted(sfWebRequest $request);
  
  /**
   * Transaction verified and failed
   *
   * @param array $post_parameters
   */
  abstract public function transactionFailed(sfWebRequest $request);
  
  /**
   * Transaction invalid (not verified)
   *
   * @param array $post_parameters
   */
  abstract public function transactionInvalid(sfWebRequest $request);
  
  /**
   * Transaction canceled (explicitly by user)
   *
   * @param array $post_parameters
   */
  abstract public function transactionCanceled(sfWebRequest $request);
}
