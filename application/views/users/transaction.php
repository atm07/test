<div class="container">
    <h2>Depsoit Page!</h2>
    <a href="<?php echo base_url('users/logout'); ?>" class="logout">Logout</a>
    <a href="<?php echo base_url('users/account'); ?>" class="deposit">Profile</a>
    <a href="<?php echo base_url('users/withdraw'); ?>" class="deposit">Withdraw</a>
    <div class="regisFrm">
    	<table>
    		<th>currency</th>
    		<th>address</th>
    		<th>amount</th>
    		<th>status</th>
    		<th>type</th>
    		<th>tx_id</th>
		    	<?php 
			    	foreach ($transaction as $key => $value) {
			    		// print_r($value);
			    		$currency = $value->currency_symbol;
			    		$address = $value->address;
			    		$amount	 = $value->amount;
			    		$status = $value->status;
			    		$tx_id = $value->tx_id;
			    		$type = $value->type;

			    ?>		
		    		<tr>
		    			<td><?php echo $currency; ?></td>
		    			<td><?php echo $address; ?></td>
		    			<td><?php echo $amount; ?></td>
		    			<td><?php echo $status; ?></td>
		    			<td><?php echo $type; ?></td>
		    			<td><?php echo $tx_id; ?></td>
		    		</tr>
			    <?php
			    	}
		    	?>
    	</table>

    </div>
</div>