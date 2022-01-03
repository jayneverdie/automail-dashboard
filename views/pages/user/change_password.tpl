<?php $this->layout('layouts/dashboard', ['title' => 'Change Password']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Change Password</h3>
    </div>
    <div class="box-body">
      <form id="formChangePassword" action="/user/change_password" method="post" class="center-360">

        <?php echo getFlashMessage(); ?>
      
        <div class="form-group">
          <label for="old_pass">Old password</label>
          <input type="password" name="old_pass" id="old_pass" class="form-control" autofocus required>
        </div>
      
        <div class="form-group">
          <label for="new_pass">New password</label>
          <input type="password" name="new_pass" id="new_pass" class="form-control" required>
        </div>
      
        <div class="form-group">
          <label for="confirm_new_pass">Confirm new password</label>
          <input type="password" name="confirm_new_pass" id="confirm_new_pass" class="form-control" required>
        </div>
      
        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $key['csrf_name']; ?>">
        <input type="hidden" name="<?php echo $value ?>" value="<?php echo $key['csrf_value']; ?>">
      
        <div class="form-group">
          <input type="submit" class="btn btn-primary" value="Submit">
        </div>
      </form>
    </div>
  </div>
</section>

<?php $this->push('scripts') ?>
<script>
  jQuery(document).ready(function ($) {
    // :)
  });
</script>
<?php $this->end() ?>