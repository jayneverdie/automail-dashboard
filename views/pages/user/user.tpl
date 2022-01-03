<?php $this->layout('layouts/dashboard', ['title' => 'All User']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">All User</h3>
    </div>
    <div class="box-body">
      <!-- button -->
      <div class="btn-control">
        <button class="btn btn-primary" id="create_user"><i class="fa fa-plus" aria-hidden="true"></i> Create</button>
        <button class="btn btn-default" id="reset_password"><i class="fa fa-refresh" aria-hidden="true"></i> Reset password</button>
      </div>
      <!-- grid -->
      <div class="table-responsive">
        <table id="grid_user" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th>Username</th>
              <th>Email</th>
              <th>Register Date</th>
              <th>Firstname</th>
              <th>Lastname</th>
              <th>Role</th>
              <th>Status</th>
            </tr>
            <tr>
              <th>Username</th>
              <th>Email</th>
              <th>Register Date</th>
              <th>Firstname</th>
              <th>Lastname</th>
              <th>Role</th>
              <th>Status</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- modal new user -->
<div class="modal" id="modal_create_user" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">Create new user</h4>
      </div>
      <div class="modal-body">
        <!-- Content -->
        <form id="form_new_user">
          <div class="form-group">
            <label for="user_Employee">Employee</label>
            <div class="row">
				     <div class="col-md-12">
					    <div class="input-group">
                  <input type="text" class="form-control" name="user_Employee" id="user_Employee" required readonly>
				          <span class="input-group-btn">
				          <button class="btn btn-info" id="select_EMP" type="button">
				        	<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
				          </button>
				          </span>
				      </div>
				    </div>
			     </div>
          </div>
          <div class="form-group">
            <label for="user_login">Username</label>
            <input type="text" name="user_login" id="user_login" class="form-control" autofocus autocomplete="off" required readonly>
            <input type="hidden" name="user_firstnameAdd" id="user_firstnameAdd" class="form-control" autofocus autocomplete="off" required>
            <input type="hidden" name="user_lastnameAdd" id="user_lastnameAdd" class="form-control" autofocus autocomplete="off" required>
          </div>
          <div class="form-group">
            <label for="user_password">Password</label>
            <input type="password" name="user_password" id="user_password" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="user_email">Email</label>
            <input type="text" name="user_email" id="user_email" class="form-control" required>
          </div>

          <button class="btn btn-primary" type="submit"><i class="fa fa-check" aria-hidden="true"></i> Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- modal reset password -->
<div class="modal" id="modal_reset_password" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">Reset password</h4>
      </div>
      <div class="modal-body">
        <!-- Content -->
        <form id="form_reset_password">
          <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required autofocus>
          </div>
          <button class="btn btn-primary" type="submit"><i class="fa fa-check" aria-hidden="true"></i> Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="modal_select_employee" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
          <span class="glyphicon glyphicon-remove"></span>
        </button>

        <h4 class="modal-title">Select User</h4>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table id="grid_user_emp" class="table table-condensed table-striped" style="width:100%">
            <thead>
              <tr>

                <th>Employee</th>
                <th>Username</th>
                <th>Surname</th>
                <th>Email</th>
              </tr>
              <tr>
                <th>Employee</th>
                <th>Username</th>
                <th>Surname</th>
                <th>Email</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $this->push('scripts') ?>
<script>
jQuery(document).ready(function ($) {

  var grid_user_callback = function() {
    // link
    $('#grid_user .--user-email').editable({
      type: 'text',
      name: 'email',
      url: '/api/v1/user/update',
      title: 'Email',
      success: function(response, newValue) {
        if (response.result === false) {
          alert(response.message);
          window.location.reload();
        }
      }
    });

    // firstname
    $('#grid_user .--user-firstname').editable({
      type: 'text',
      name: 'firstname',
      url: '/api/v1/user/update',
      title: 'Firstname',
      success: function(response, newValue) {
        if (response.result === false) {
          alert(response.message);
          window.location.reload();
        }
      }
    });

    // Lastname
    $('#grid_user .--user-lastname').editable({
      type: 'text',
      name: 'lastname',
      url: '/api/v1/user/update',
      title: 'Lastname',
      success: function(response, newValue) {
        if (response.result === false) {
          alert(response.message);
          window.location.reload();
        }
      }
    });

    call_ajax('get', '/api/v1/role/active')
    .done(function (data) {
      // Role
      $('#grid_user .--user-role').editable({
        type: 'select',
        name: 'role',
        url: '/api/v1/user/update',
        title: 'Role',
        source: pack_dd(data, 'id', 'role_name'),
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
      // status
      $('#grid_user .--user-status').editable({
        type: 'select',
        name: 'status',
        url: '/api/v1/user/update',
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
    // END
  };

  var grid_user_emp_callback = function() {

  };



  loadGrid({
    el: '#grid_user',
    processing: true,
    serverSide: true,
    deferRender: true,
    searching: true,
    order: [],
    orderCellsTop: true,
    modeSelect: "single",
    ajax: {
      url: '/api/v1/user/all',
      method: 'post'
    },
    fnDrawCallback: grid_user_callback,
    columns: [
      { data: "user_login"},
      { data: 'user_email'},
      { data: "user_registered"},
      { data: "user_firstname"},
      { data: "user_lastname"},
      { data: 'user_role'},
      { data: 'status_name'}
    ],
    columnDefs: [
      {
        render: function(data, type, row) {
          return '<a href="javascript:void(0)" class="--user-email" data-pk="'+row.id+'">'+isNull(data)+'</a>';
        }, targets: 1
      },
      {
        render: function(data, type, row) {
          return '<a href="javascript:void(0)" class="--user-firstname" data-pk="'+row.id+'">'+isNull(data)+'</a>';
        }, targets: 3
      },
      {
        render: function(data, type, row) {
          return '<a href="javascript:void(0)" class="--user-lastname" data-pk="'+row.id+'">'+isNull(data)+'</a>';
        }, targets: 4
      },
      {
        render: function (data, type, row) {
          return '<a href="javascript:void(0)" class="--user-role" data-pk="'+row.id+'">'+isNull(data)+'</a>';
        }, targets: 5
      },
      {
        render: function (data, type, row) {
          return '<a href="javascript:void(0)" class="--user-status" data-pk="'+row.id+'">'+data+'</a>';
        }, targets: 6
      }
    ]
  });

  $('#create_user').on('click', function () {
    $('#modal_create_user').modal({backdrop: 'static'});
    $('input[name=user_login]').val('');
    $('input[name=user_Employee]').val('');
  //  $('input[name=user_Employee]').val('');

  });

  $('#form_new_user').submit(function(e) {
    e.preventDefault();
    if (confirm('Are you sure ?')) {
      call_ajax('post', '/api/v1/user/create', {
        user_login: $('#user_login').val(),
        user_password: $('#user_password').val(),
        user_Employee: $('#user_Employee').val(),
        user_firstnameAdd: $('#user_firstnameAdd').val(),
        user_lastnameAdd: $('#user_lastnameAdd').val(),
        user_email: $('#user_email').val()
      }).done(function (data) {
        if (data.result === true) {
          $('#modal_create_user').modal('hide');
          $('#form_new_user').trigger('reset');
          reloadGrid('#grid_user', grid_user_callback);
        } else {
          alert(data.message);
        }
      });
    }
  });

  $('#user_login').keyup(function () {
    $('#user_login').val($('#user_login').val().toLowerCase().replace(/[\s-'.@#\\/+=*%&!$?)({}]/g, "_"));
  });

  $('#reset_password').on('click', function () {
    var rowdata = rowSelected('#grid_user');
    if (rowdata.length !== 0) {
      $('#modal_reset_password').modal({backdrop: 'static'});
    } else {
      alert('Please select row!');
    }
  });

  $('#form_reset_password').submit(function(e) {
    e.preventDefault();

    var rowdata = rowSelected('#grid_user')[0];

    call_ajax('post', '/api/v1/user/reset_password', {
      id: rowdata.id,
      role_id: rowdata.role_id,
      new_password: $('#new_password').val()
    }).done(function (data) {
      if (data.result === true) {
        $('#modal_reset_password').modal('hide');
        $('#form_reset_password').trigger('reset');
        reloadGrid('#grid_user', grid_user_callback);
      } else {
        alert(data.message);
      }
    });
  });

  $('#select_EMP').on('click', function () {
    $('#modal_select_employee').modal({backdrop: 'static'});
  });

  // loadGrid({
  //   el: '#grid_user_emp',
  //   processing: false,
  //   serverSide: false,
  //   deferRender: true,
  //   searching: true,
  //   order:[],
  //   modeSelect: "single",
  //   ajax: "/api/v1/user/getEmployee",
  //   fnDrawCallback: grid_user_callback,
  //   columns: [
  //     { data: "CODEMPID"},
  //     { data: 'EMPNAME'},
  //     { data: "EMPLASTNAME"}
  //   ]
  // });

  loadGrid({
    el: '#grid_user_emp',
    processing: true,
    serverSide: true,
    deferRender: true,
    searching: true,
    order: [],
    orderCellsTop: true,
    modeSelect: "single",
    ajax: {
      url: '/api/v1/user/getEmployee',
      method: 'post'
    },
    fnDrawCallback: grid_user_emp_callback,
    columns: [
          { data: "CODEMPID"},
          { data: 'EMPNAME'},
          { data: "EMPLASTNAME"},
          { data: "EMAIL"}
    ]
  });


  $('#grid_user_emp').on('dblclick', function () {
    var rowdata = rowSelected('#grid_user_emp');
    $('input[name=user_Employee]').val(rowdata[0].CODEMPID);
    $('input[name=user_firstnameAdd]').val(rowdata[0].EMPNAME);
    $('input[name=user_lastnameAdd]').val(rowdata[0].EMPLASTNAME);
    $('input[name=user_login]').val(rowdata[0].CODEMPID);
    $('input[name=user_email]').val(rowdata[0].EMAIL);
    $('#modal_select_employee').modal('hide');
    $('input[name=user_password]').val('').focus();

  });
});
</script>
<?php $this->end() ?>
