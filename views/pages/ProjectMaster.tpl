<?php $this->layout('layouts/dashboard', ['title' => 'Project Lists']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Project Lists</h3>
    </div>
    <div class="box-body">
		<!-- btn -->
		<div class="btn-control">
	        <button class="btn btn-primary" id="create_emaillist"><i class="fa fa-plus" aria-hidden="true"></i> Create</button>
	        <button class="btn btn-danger" id="delete_emaillists"><i class="fa fa-close" aria-hidden="true"></i> Delete</button>
	    </div>
		<!-- grid -->
		<table id="grid_project_lists" class="table table-condensed table-striped" style="width:100%">
			<thead>
				<tr>
					<th>ID</th>
					<th>Project name</th>
					<th>Use Port</th>
					<!-- <th>Email</th>
					<th>Port</th>
					<th>Email Type</th>
					<th>Project Name</th>
					<th>Email Category</th>
					<th>Status</th> -->
				</tr>
			</thead>
		</table>
    </div>
  </div>
</section>

<!-- modal new emaillist -->
<div class="modal" id="modal_create_Project" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">Create new emaillist</h4>
      </div>
      <div class="modal-body">
        <!-- Content -->
        <form id="form_new_projectlist">
          <div class="form-group">
            <label for="email_list">Project</label>
            <input type="text" name="user_p" id="user_p" class="form-control" autofocus autocomplete="off" required>
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
	// code here
	var grid_callback = function(settings, json) {
		// custmer code
		// $('#grid_email_lists .--col-customer-code').editable({
		// 	type: 'text',
		// 	name: 'customer_code',
		// 	url: '/api/v1/email/update_emailLists',
		// 	title: 'Customer Code',
		// 	success: function(response, newValue) {
		// 		if (response.result === false) {
		// 			alert(response.message);
		// 			window.location.reload();
		// 		}
		// 	}
		// });
    //
		// // email
		// $('#grid_email_lists .--col-email').editable({
		// 	type: 'text',
		// 	name: 'email',
		// 	url: '/api/v1/email/update_emailLists',
		// 	title: 'Email',
		// 	success: function(response, newValue) {
		// 		if (response.result === false) {
		// 			alert(response.message);
		// 			window.location.reload();
		// 		}
		// 	}
		// });
    //
		// // port
		// $('#grid_email_lists .--col-port').editable({
		// 	type: 'text',
		// 	name: 'port',
		// 	url: '/api/v1/email/update_emailLists',
		// 	title: 'Port',
		// 	success: function(response, newValue) {
		// 		if (response.result === false) {
		// 			alert(response.message);
		// 			window.location.reload();
		// 		}
		// 	}
		// });
    //
		// // email type
		// call_ajax('get', '/api/v1/email/lists_type')
	  //     	.done(function(data) {
	  //       $('#grid_email_lists .--col-email-type').editable({
	  //         type: 'select',
	  //         name: 'email_type',
	  //         url: '/api/v1/email/update_emailLists',
	  //         title: 'EmailType',
	  //         source: pack_dd(data, 'ID', 'Description'),
	  //         success: function(response, newValue) {
	  //           if (response.result === false) {
	  //             alert(response.message);
	  //             window.location.reload();
	  //           }
	  //         }
	  //       });
	  //   });
    //
    //
		// // email project
		// call_ajax('get', '/api/v1/email/lists_project')
	  //     	.done(function(data) {
	  //       $('#grid_email_lists .--col-project').editable({
	  //         type: 'select',
	  //         name: 'project_id',
	  //         url: '/api/v1/email/update_emailLists',
	  //         title: 'Project',
	  //         source: pack_dd(data, 'ProjectID', 'ProjectName'),
	  //         success: function(response, newValue) {
	  //           if (response.result === false) {
	  //             alert(response.message);
	  //             window.location.reload();
	  //           }
	  //         }
	  //       });
	  //   });
    //
		// // category
		// call_ajax('get', '/api/v1/email/lists_category')
	  //     	.done(function(data) {
	  //       $('#grid_email_lists .--col-category').editable({
	  //         type: 'select',
	  //         name: 'email_category',
	  //         url: '/api/v1/email/update_emailLists',
	  //         title: 'Email Category',
	  //         source: pack_dd(data, 'ID', 'Description'),
	  //         success: function(response, newValue) {
	  //           if (response.result === false) {
	  //             alert(response.message);
	  //             window.location.reload();
	  //           }
	  //         }
	  //       });
	  //   });

		// status
		call_ajax('get', '/api/v1/status/all')
			.done(function(data) {
			$('#grid_project_lists .--user-ProjectName').editable({
				type: 'text',
				name: 'project_name',
				url: '/api/v1/Project/update_project',
				title: 'ProjectName',
				source: pack_dd(data, 'id', 'status_name'),
				success: function(response, newValue) {
					if (response.result === false) {
						alert(response.message);
						window.location.reload();
					}
				}
			});
		});
		
		call_ajax('get', '/api/v1/status/all')
    	.done(function(data) {
      $('#grid_project_lists .--user-UsePort').editable({
        type: 'select',
        name: 'use_port',
        url: '/api/v1/Project/update_project',
        title: 'UsePort',
        source: pack_dd(data, 'id', 'status_name'),
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            // window.location.reload();
          }
        }
      });
    });
		// end


	}

	loadGrid({
		el: '#grid_project_lists',
		deferRender: true,
		order:[],
		modeSelect: "single",
		ajax: "/api/v1/Project/lists",
		fnDrawCallback: grid_callback,
		columns: [
			{ data: "ProjectID" },
			{ data: "ProjectName" },
			{ data: "status_name"}

		],
    columnDefs: [
      {
        render: function(data, type, row) {
          return '<a href="javascript:void(0)" class="--user-ProjectName" data-pk="'+row.ProjectID+'">'+isNull(data)+'</a>';
        }, targets: 1
      },
			{
        render: function(data, type, row) {
          return '<a href="javascript:void(0)" class="--user-UsePort" data-pk="'+row.ProjectID+'">'+isNull(data)+'</a>';
        }, targets: 2
      }

    ]

	});

  $('#create_emaillist').on('click', function () {
	    $('#modal_create_Project').modal({backdrop: 'static'});
	});

  $('#form_new_projectlist').submit(function(e) {
    e.preventDefault();
    if (confirm('Are you sure ?')) {
      call_ajax('post', '/api/v1/Project/create', {
        user_p: $('#user_p').val(),
        // user_password: $('#user_password').val(),
        //   user_Employee: $('#user_Employee').val(),
      }).done(function (data) {
        if (data.result === true) {
          // $('#modal_create_user').modal('hide');
          // $('#form_new_user').trigger('reset');
          // reloadGrid('#grid_user', grid_user_callback);
        alert(data.message);
        } else {
          alert(5678);
        }
      });
    }
  });

  $('#delete_emaillists').on('click', function() {
      var rowdata = rowSelected('#grid_project_lists');
      if (rowdata.length !== 0) {
        if (confirm('Aru you sure?')) {
          call_ajax('post', '/api/v1/Project/delete_project', {
            id: rowdata[0].ProjectID
          }).done(function(data) {
            if (data.result === true) {
              reloadGrid('#grid_email_lists', grid_callback);
            } else {
              alert(data.message);
            }
          });
          // alert(rowdata[0].ProjectID);

        }
      } else {
        alert('Please select row!');
      }
    });




  });
</script>
<?php $this->end(); ?>
