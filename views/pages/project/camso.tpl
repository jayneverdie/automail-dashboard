<?php $this->layout('layouts/dashboard', ['title' => 'CAMSO']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">CAMSO</h3>
    </div>
    <div class="box-body">
      <!-- grid -->
			<table id="grid_camso" class="table table-condensed table-striped" style="width:100%">
				<thead>
					<tr>
						<th>Name</th>
						<th>Status</th>
					</tr>
        </thead>
			</table>
    </div>
  </div>
</section>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {
    // code here

    loadGrid({
      el: '#grid_camso',
      deferRender: true,
      order: [],
      modeSelect: "single",
			ajax: "/api/v1/category/all",
			// fnDrawCallback: grid_category_callback,
      columns: [
        { data: "category_name" },
        { data: "status_name" }
			],
			columnDefs: [
				{
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--category-name" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 0
				},
				{
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--category-status" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 1
        }
			]
    });
  });
</script>
<?php $this->end(); ?>