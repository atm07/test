<div class="container">
    <h2>Depsoit Page!</h2>
    <a href="<?php echo base_url('users/logout'); ?>" class="logout">Logout</a>
    <a href="<?php echo base_url('users/account'); ?>" class="deposit">Profile</a>
    <div class="regisFrm">
        <p><b>Address: </b><?php echo $address_details['address']; ?></p>
        <p><b>QR Code: </b><img src="<?php echo $address_details['url']; ?>" class="deposit" alt="<?php echo $address_details['address']; ?>"></p>
    </div>
</div>

<!-- Script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script type='text/javascript'>
 	$(document).ready(function(){

 		setTimeout(function(){ 

 			alert("Hello"); 
		    $.ajax({
		     url:'<?=base_url()?>users/deposit_history/<?php echo $address_details['address']; ?>',
		     method: 'GET',
		     success: function(response){
		     }
		    });
 		}, 3000);
 	   	
	  
	});
 </script>