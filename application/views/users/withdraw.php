<div class="container">
    <h2>Withdraw Page!</h2>

    <a href="<?php echo base_url('users/logout'); ?>" class="logout">Logout</a>
    <a href="<?php echo base_url('users/account'); ?>" class="deposit">Profile</a>
    <a href="<?php echo base_url('users/transaction_history'); ?>" class="deposit">Transaction History</a>
	
    <!-- Status message -->
    <?php  
        if(!empty($success_msg)){ 
            echo '<p class="status-msg success">'.$success_msg.'</p>'; 
        }elseif(!empty($error_msg)){ 
            echo '<p class="status-msg error">'.$error_msg.'</p>'; 
        } 
    ?>
	
    <!-- Login form -->
    <div class="regisFrm">
        <form action="" method="post">
            <div class="form-group">
                <input type="text" name="address" placeholder="Address" required="">
                <?php echo form_error('address','<p class="help-block">','</p>'); ?>
            </div>
            <div class="form-group">
                <input type="text" name="amount" placeholder="Amount" required="">
                <?php echo form_error('amount','<p class="help-block">','</p>'); ?>
            </div>
            <div class="send-button">
                <input type="submit" name="withdrawSubmit" value="Withdraw">
            </div>
        </form>
    </div>
</div>
