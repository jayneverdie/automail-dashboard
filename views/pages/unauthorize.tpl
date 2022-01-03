<?php $this->layout('layouts/clean', ['title' => 'Unauthorized']); ?>

<div style="text-align: center; margin: 7%;">
  <p style="font-size: 5em; margin: 0 auto;">401</p>
  <p style="margin: 1% 0 2% 0;">Your are not unauthorized to access this section!</p>
  <p><a href="<?php echo APP_ROOT;?>">Go back</a></p>
</div>

<?php $this->push('scripts') ?>
<script>
  jQuery(document).ready(function($) {
    console.log("document rendered.");
  });
</script>
<?php $this->end() ?>
