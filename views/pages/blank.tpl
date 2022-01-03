<?php $this->layout('layouts/dashboard', ['title' => 'Blank Page']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Header</h3>
    </div>
    <div class="box-body">
      Content
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