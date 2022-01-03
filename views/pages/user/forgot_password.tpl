<?php $this->layout('layouts/dashboard', ['title' => 'Forgot Password']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Forgot Password</h3>
    </div>
    <div class="box-body">
			<?php echo getFlashMessage(); ?>
      <form action="/user/forgot_password/check" method="post">
				<div class="form-group">
					<label for="email">Email</label>
					<input type="email" name="email" class="form-control" 
						id="email" 
						autocomplete="off" 
						autofocus 
						required
						style="max-width: 300px;" />
				</div>
				<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $key['csrf_name']; ?>">
  			<input type="hidden" name="<?php echo $value ?>" value="<?php echo $key['csrf_value']; ?>">
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
    </div>
  </div>
</section>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {
    // code here
  });
</script>
<?php $this->end(); ?>