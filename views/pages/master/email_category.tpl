<?php $this->layout('layouts/dashboard', ['title' => 'Email Category']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Email Category</h3>
    </div>
    <div class="box-body">
			<!-- button -->
			<div class="btn-control">
				<button type="button" id="create_email_category" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> Create</button>
			</div>
      <!-- Table -->
      <div class="table-responsive">
        <table id="grid_email_category" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Description</th>
            </tr>
          </thead>
				</table>
			</div>
    </div>
  </div>
</section>

<!-- modal -->
<div class="modal" id="modal_email_category" tabindex="-1" role="dialog">
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
        <form id="form_email_category">
          <div class="form-group">
            <label for="email_category">Email Category</label>
            <input type="text" name="email_category" class="form-control" autocomplete="off" required>
          </div>
          <button class="btn btn-primary" type="submit"><i class="fa fa-check" aria-hidden="true"></i> Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {

		// callback
		var grid_email_category_callback = function(settings, json) {  
      $('#grid_email_category .--description').editable({
        type: 'text',
        name: 'description',
        url: '/api/v1/master/update_emailcategory',
        title: 'Description',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
      });
		};
    // code here
		loadGrid({
      el: '#grid_email_category',
      processing: false,
      serverSide: false,
      deferRender: true,
      searching: true,
      modeSelect: "single",
      ajax: "/api/v1/master/email_category",
      fnDrawCallback: grid_email_category_callback,
      columns: [
        { data: "ID", width: 50 },
		    { data: 'Description'}
      ],
			columnDefs: [
				{
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--description" data-pk="'+row.ID+'">'+isNull(data)+'</a>';
          }, targets: 1
				}
			]
		});

    $('#create_email_category').on('click', function () {
      $('#modal_email_category').modal({backdrop: 'static'});
      $('#form_email_category').trigger('reset');
    });

    $('#form_email_category').submit(function(e) {
      e.preventDefault();
      call_ajax('post', '/api/v1/master/create_emailcategory', {
        email_category: $('input[name=email_category]').val(),
      }).done(function (data) {
        if (data.result === true) {
          $('#form_email_category').trigger('reset');
          $('#modal_email_category').modal('hide');
          reloadGrid('#grid_email_category', grid_email_category_callback);
        } else {
          $('#form_email_category').trigger('reset');
          alert(data.message);
        }
      });
    }); 

  });
</script>
<?php $this->end(); ?>