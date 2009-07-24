<?php
  if(isset($transaction)) {
  	$parameters = $transaction->getFields();
  } else {
  	$parameters = $sf_request->getPostParameters();
  }
?>
<?php if(count($parameters)):?>
<table border="1">
<?php foreach($parameters as $key => $field): ?>
  <?php // bold for specific code (easier to read)  ?>
  <?php if (in_array($key, array('currency_code', 'amount', 'item_name', 'payment_gross', 'mc_currency'))):?>
  <tr style="font-weight: bold; font-size: 1.1em;">
  <?php else:?>
  <tr>
  <?php endif;?>
    <td><?php echo $key; ?></td>
    <td><?php echo $field; ?></td>
  </tr>
<?php endforeach;?>
</table>
<?php else:?>
<em>No parameter</em>
<?php endif;?>