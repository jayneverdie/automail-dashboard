<?php $this->layout('layouts/dashboard', ['title' => 'Profile']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Profile</h3>
    </div>
    <div class="box-body">
      <form id="formProfile" method="post" action="/user/profile" enctype="multipart/form-data" class="center-360">
        <?php echo getFlashMessage(); ?>
        
        <div class="form-group">

          <?php
            $path = './assets/images/'.$user_data[0]['user_login'].'/';
            $scan = scandir($path);
          ?>

          <?php 
            if (count($scan)>=3) {
          ?>
            <img src="/assets/images/<?php echo $user_data[0]['user_login']; ?>/<?php echo $user_data[0]['user_login']; ?>" class="img-circle" alt="User Image" style="width: 180px; height: 180px;">
          <?php }else{ ?>
            <img src="/assets/images/avatar" class="img-circle" alt="User Image" style="width: 180px; height: 180px;">
          <?php } ?>

          <label for="user_img">
            <input type="file" class='btn btn-default' name="InputFileUpload" id="InputFileUpload">
          </label>
        </div>

        <div class="form-group">
          <label for="user_login">Username</label>
          <input type="text" name="user_login" id="user_login" class="form-control" value="<?php echo $user_data[0]['user_login']; ?>"
            readonly>
        </div>
      
        <div class="form-group">
          <label for="user_email">Email</label>
          <input type="email" name="user_email" id="user_email" class="form-control" value="<?php echo $user_data[0]['user_email']; ?>" autocomplete="off">
        </div>
      
        <div class="form-group">
          <label for="user_registered_date">Register Date</label>
          <input type="text" name="user_registered_date" id="user_registered_date" class="form-control" value="<?php echo $user_data[0]['user_registered']; ?>"
            readonly>
        </div>
      
        <div class="form-group">
          <label for="user_firstname">First Name</label>
          <input type="text" name="user_firstname" id="user_firstname" class="form-control" value="<?php echo $user_data[0]['user_firstname']; ?>" autocomplete="off">
        </div>
      
        <div class="form-group">
          <label for="user_lastname">Last Name</label>
          <input type="text" name="user_lastname" id="user_lastname" class="form-control" value="<?php echo $user_data[0]['user_lastname']; ?>" autocomplete="off">
        </div>
      
        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $key['csrf_name']; ?>">
        <input type="hidden" name="<?php echo $value ?>" value="<?php echo $key['csrf_value']; ?>">
      
        <div class="form-group">
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</section>

<?php $this->push('scripts') ?>
<script>
  jQuery(document).ready(function ($) {
    // code here
  });
</script>
<?php $this->end() ?>