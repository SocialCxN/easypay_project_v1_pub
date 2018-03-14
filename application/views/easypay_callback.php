<form name="easypayconfirmform" action="<?php echo $easypayConfirmPage ?>" method="POST">
	<input name="auth_token" value="<?php echo $_GET['auth_token'] ?>" hidden = "true"/>
	<input name="postBackURL" value="<?php echo $merchantStatusPage ?>" hidden = "true"/>	
</form>
<script type="text/javascript">
    document.easypayconfirmform.submit();
</script>