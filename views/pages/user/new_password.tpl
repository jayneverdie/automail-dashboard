<?php $this->layout('layouts/dashboard', ['title' => 'New Password']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">New Password</h3>
    </div>
    <div class="box-body">
			<?php echo getFlashMessage(); ?>
      <form id="updatePassword" action="/user/new_password/save" method="post">
				<div class="form-group">
					<label for="email">Email</label>
					<input type="email" name="email" class="form-control" 
						id="email" 
						autocomplete="off" 
						required
						style="max-width: 300px;"
						readonly
						value="<?php echo $email; ?>" />
				</div>

				<div class="form-group">
					<label for="new_password">New password</label>
					<input type="password" 
						name="new_password" 
						id="new_password" 
						autofocus
						required
						class="form-control" 
						style="max-width: 300px;"
						placeholder="รหัสผ่านใหม่">
				</div>

				<div class="form-group">
					<label for="confirm_new_password">Confirm new password</label>
					<input type="password" 
						name="confirm_new_password" 
						id="confirm_new_password" 
						required
						class="form-control" 
						style="max-width: 300px;"
						placeholder="ยืนยันรหัสผ่านใหม่">
				</div>
				<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $key['csrf_name']; ?>">
  			<input type="hidden" name="<?php echo $value ?>" value="<?php echo $key['csrf_value']; ?>">
				<button type="button" id="submitNewPassword" class="btn btn-primary">Submit</button>
			</form>
    </div>
  </div>
</section>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {
    // code here
		$('#submitNewPassword').on('click', function() {
			var new_password = $('#new_password').val();
			var confirm_new_password = $('#confirm_new_password').val();
			if (new_password !== confirm_new_password) {
				alert('รหัสผ่านไม่ตรงกัน');
			} else if ($.trim(new_password) === '' || $.trim(confirm_new_password) === '') {
				alert('รหัสผ่านต้องไม่เป็นค่าว่าง');
			} else {
				$('#updatePassword').submit();
			}
		});
  });
</script>
<?php $this->end(); ?>