<?php $this->layout('layouts/dashboard', ['title' => 'Category']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Category</h3>
    </div>
    <div class="box-body">
			<!-- button -->
      <div class="btn-control">
				<button class="btn btn-primary" id="create"><i class="fa fa-plus" aria-hidden="true"></i> Create</button>
			</div>
			<!-- grid -->
			<table id="grid_category" class="table table-condensed table-striped" style="width:100%">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Status</th>
					</tr>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Status</th>
					</tr>
				</thead>
			</table>
    </div>
  </div>
</section>

<!-- modal create -->
<!-- Modal -->
<div class="modal" id="modal_create_category" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
					<span class="glyphicon glyphicon-remove"></span>
				</button>
				<h4 class="modal-title">Create</h4>
			</div>
			<div class="modal-body">
				<!-- Content -->
				<form id="form_create_category">
					<div class="form-group">
						<label for="category_name">Category Name</label>
						<input type="text" name="category_name" id="category_name" class="form-control" required autofocus autocomplete="off">
					</div>
					<button type="submit" class="btn btn-primary"><i class="fa fa-check" aria-hidden="true"></i> Submit</button>
				</form>
			</div>
		</div>
	</div>
</div>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {

		// callback
		var grid_category_callback = function(settings, json) {
			// name
      $('#grid_category .--category-name').editable({
        type: 'text',
        name: 'name',
        url: '/api/v1/category/update',
        title: 'Name',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
			});
			
			// status
			call_ajax('get', '/api/v1/status/all')
      .done(function(data) {
        // status
        $('#grid_category .--category-status').editable({
          type: 'select',
          name: 'status',
          url: '/api/v1/category/update',
          title: 'Status',
          source: pack_dd(data, 'id', 'status_name'),
          success: function(response, newValue) {
            if (response.result === false) {
              alert(response.message);
              window.location.reload();
            }
          }
        });
			});
			// end
		};

		loadGrid({
      el: '#grid_category',
      processing: true,
			serverSide: true,
			deferRender: true,
			searching: true,
			order: [],
			orderCellsTop: true,
      modeSelect: "single",
			ajax: {
				url: '/api/v1/category/all',
				method: 'post'
			},
			fnDrawCallback: grid_category_callback,
      columns: [
				{ data: "id", width: '50px'},
        { data: "category_name" },
        { data: "status_name" }
			],
			columnDefs: [
				{
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--category-name" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 1
				},
				{
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--category-status" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 2
        }
			]
		});

		$('#create').on('click', function() {
			$('#modal_create_category').modal({backdrop: 'static'});
			$('#category_name').val('').focus();
		});

		$('#form_create_category').submit(function(e) {
			e.preventDefault();
			call_ajax('post', '/api/v1/category/create', {
				category_name: $('#category_name').val()
			}).done(function(data) {
				if (data.result === true) {
					$('#modal_create_category').modal('hide');
					$('#form_create_category').trigger('reset');
					reloadGrid('#grid_category', grid_category_callback);
				} else {
					alert(data.message);
				}
			});
		});
  });
</script>
<?php $this->end(); ?>