<?php $this->layout('layouts/dashboard', ['title' => 'Email Mapping']);?>

<section class="content">
  <div class="box box-primary">

    <div class="box-header with-border">
      <h3 class="box-title">Email Mapping</h3>
      <div class="box-tools pull-right">
        <div class="btn-group">
          <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <div style="font-size: 1.5rem;">
            <i class="fa fal fa-ellipsis-h"></i>
          </div>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li>
              <div style="font-size: 1.5rem;">&nbsp;&nbsp;
                <i class="fa fal fa-history"></i>
                <a href="#" class="--view-log" style="font-size: 1.2rem;"> View edit history</a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="box-body">
			<!-- button -->
			<div class="btn-control">
        <button class="btn btn-primary" id="create">Create</button>
        <button class="btn btn-danger" id="delete">Delete</button>
			</div>
      <!-- Table -->
      <div class="table-responsive">
        <table id="grid_email_mapping" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th>Customer Code</th>
              <th>Email</th>
            </tr>
            <tr>
              <th>Customer Code</th>
              <th>Email</th>
            </tr>
          </thead>
				</table>
			</div>
    </div>
  </div>
</section>

<!-- Create -->
<div class="modal" id="modal_create" tabindex="-1" role="dialog">
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
        <form id="form_create">
          <div class="form-group">
            <label for="customer">Customer Code</label>
            <input type="text" name="customer" id="customer" class="form-control" autofocus required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {

		// callback
		var grid_email_mapping_callback = function(settings, json) {
			// customer code
      $('#grid_email_mapping .--customer-code').editable({
        type: 'text',
        name: 'customer_code',
        url: '/api/v1/email/update',
        title: 'Customer Code',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
			});

			// email
      $('#grid_email_mapping .--email').editable({
        type: 'text',
        name: 'email',
        url: '/api/v1/email/update',
        title: 'Email',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
			});
		};

    // load grid
		loadGrid({
      el: '#grid_email_mapping',
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      ajax: {
        url: '/api/v1/email/all_mapping',
        method: 'post'
      },
      fnDrawCallback: grid_email_mapping_callback,
      columns: [
        { data: "CustomerCode" },
				{ data: 'Email'}
      ],
			columnDefs: [
				{
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--customer-code" data-pk="'+row.ID+'">'+isNull(data)+'</a>';
          }, targets: 0
				},
				{
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--email" data-pk="'+row.ID+'">'+isNull(data)+'</a>';
          }, targets: 1
				}
			]
		});

    $('#delete').on('click', function() {
      if (confirm('Are you sure?')) {
        var rowdata = rowSelected('#grid_email_mapping');
        if (rowdata.length !== 0) {
          call_ajax('post', '/api/v1/email/mapping/delete', {
            id: rowdata[0].ID
          }).done(function(data) {
            if (data.result === false) {
              alert(data.message);
            } else {
              reloadGrid('#grid_email_mapping');
            }
          });
        } else {
          alert('please select row!');
        }
      }
    });

    $('#create').on('click', function() {
      $('#modal_create').modal({backdrop: 'static'});
    });

    $('#form_create').submit(function(e) {
      e.preventDefault();
      call_ajax('post', '/api/v1/email/mapping/create', {
        customer: $('#customer').val(),
        email: $('#email').val()
      }).done(function(data) {
        if ( data.result === true) {
          $('#modal_create').modal('hide');
          reloadGrid('#grid_email_mapping');
        } else {
          alert(data.message);
        }
      });
    });

    // END
  });
</script>
<?php $this->end(); ?>
