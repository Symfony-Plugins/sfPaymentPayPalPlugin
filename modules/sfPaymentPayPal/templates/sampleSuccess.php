<?php	use_helper('Payment'); ?>

<h1>Sample : Pay with PayPal</h1>
<p>
  The button "Pay with PayPal" below will send you to PayPal using the following information.
</p>

<?php include_partial('parameters', array('transaction' => $transaction)); ?>

<br />

<?php echo payment_form_tag_for($transaction->getGateway()); ?>
  <input type="submit" value="Pay with PayPal">
</form>