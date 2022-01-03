<?php $this->layout('layouts/dashboard', ['title' => 'Role']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Role</h3>
    </div>
    <div class="box-body">
      <!-- Button -->
      <div class="btn-control">
        <button class="btn btn-primary" id="create"><i class="fa fa-plus" aria-hidden="true"></i> Create</button>
        <button class="btn btn-default" id="view_capability"><i class="fa fa-eye" aria-hidden="true"></i> View capability</button>
      </div>
      <!-- Grid -->
      <table id="grid_role" class="table table-condensed table-striped" style="width:100%">
        <thead>
          <tr>
            <th>Role</th>
            <th>Status</th>
            <th>DefaultPage</th>
          </tr>
          <tr>
            <th>Role</th>
            <th>Status</th>
            <th>DefaultPage</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>

<!-- modal view capability -->
<div class="modal" id="modal_view_capability" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">Capability</h4>
      </div>
      <div class="modal-body">
        <!-- Content -->
        <form id="form_update_capability">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab_menu_page" data-toggle="tab" aria-expanded="false">Menu Page</a></li>
              <li><a href="#tab_menu_api" data-toggle="tab" aria-expanded="false">Menu API</a></li>
              <li><a href="#tab_admin" data-toggle="tab" aria-expanded="false">Administrator</a></li>
              <li><a href="#tab_user" data-toggle="tab" aria-expanded="false">User</a></li>
              <li><a href="#tab_role" data-toggle="tab" aria-expanded="false">Role</a></li>
              <li><a href="#tab_capability" data-toggle="tab" aria-expanded="false">Capability</a></li>
              <li><a href="#tab_category" data-toggle="tab" aria-expanded="false">Category</a></li>
              <li><a href="#tab_media" data-toggle="tab" aria-expanded="false">Media</a></li>
              <li><a href="#tab_email" data-toggle="tab" aria-expanded="false">Email</a></li>
            </ul> 
            <div class="tab-content">
              <!-- tab menu page -->
              <div class="tab-pane active" id="tab_menu_page">
                <p>
                  <div id="menu_page_capability"></div>
                </p>
              </div>
              <!-- tab menu api -->
              <div class="tab-pane" id="tab_menu_api">
                <p>
                  <div id="menu_api_capability"></div>
                </p>
              </div>
              <!-- tab admin-->
              <div class="tab-pane" id="tab_admin">
                <p>
                  <div id="admin_capability"></div> 
                </p>
              </div>
              <!-- tab user -->
              <div class="tab-pane" id="tab_user">
                <p>
                  <div id="user_capability"></div>
                </p>
              </div>
              <!-- tab role -->
              <div class="tab-pane" id="tab_role">
                <p>
                  <div id="role_capability"></div>
                </p>
              </div>
              <!-- tab capability -->
              <div class="tab-pane" id="tab_capability">
                <p>
                  <div id="capability_capability"></div>
                </p>
              </div>
              <!-- tab category -->
              <div class="tab-pane" id="tab_category">
                <p>
                  <div id="category_capability"></div>
                </p>
              </div>
              <!-- tab media -->
              <div class="tab-pane" id="tab_media">
                <p>
                  <div id="media_capability"></div>
                </p>
              </div>
              <!-- tab media -->
              <div class="tab-pane" id="tab_email">
                <p>
                  <div id="email_capability"></div>
                </p>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- modal create role -->
<div class="modal" id="modal_create_role" tabindex="-1" role="dialog">
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
        <form id="form_create_role">
          <div class="form-group">
            <label for="role_name">Role name</label>
            <input type="text" name="role_name" id="role_name" class="form-control" autocomplete="off" autofocus required>
          </div>
          <button class="btn btn-primary" type="submit"><i class="fa fa-check" aria-hidden="true"></i> Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $this->push('scripts') ?>
<script>

  jQuery(document).ready(function ($) {

    var category = {
      page: 1,
      api: 2,
      admin: 3,
      user: 4,
      role: 5,
      capability: 6,
      category: 7,
      media: 8,
      email: 9
    };

    var grid_role_callback = function() {
      // role name
      $('#grid_role .--role-name').editable({
        type: 'text',
        name: 'name',
        url: '/api/v1/role/update',
        title: 'Name',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
      });

      call_ajax('get', '/api/v1/status/all')
      .done(function(data) {
        // role status
        $('#grid_role .--role-status').editable({
          type: 'select',
          name: 'status',
          url: '/api/v1/role/update',
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

      call_ajax('get', '/api/v1/role/feed/menu')
      .done(function(data) {
        // role status
        $('#grid_role .--role-menu').editable({
          type: 'select',
          name: 'menupage',
          url: '/api/v1/role/update',
          title: 'DefaultPage',
          source: pack_dd(data, 'id', 'menu_name'),
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

    loadGrid({
      el: '#grid_role',
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      ajax: {
        url: '/api/v1/role/all',
        method: 'post'
      },
      fnDrawCallback: grid_role_callback,
      columns: [
        { data: "role_name"},
        { data: 'status_name'},
        { data: "menu_name"}
      ],
      columnDefs: [
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--role-name" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 0
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--role-status" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 1
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--role-menu" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 2
        }
      ]
    });
    
    // create role
    $('#create').on('click', function () {
      $('#modal_create_role').modal({backdrop: 'static'});
      $('#form_create_role').trigger('reset');
    });

    // submit form create role
    $('#form_create_role').submit(function(e) {
      e.preventDefault();
      call_ajax('post', '/api/v1/role/create', {
        role_name: $('#role_name').val()
      }).done(function(data) {
        if (data.result === true) {
          $('#modal_create_role').modal('hide');
          $('#form_create_role').trigger('reset');
          reloadGrid('#grid_role', grid_role_callback);
        } else {
          alert(data.message);
        }
      });
    });
        
    // view capability
    $('#view_capability').on('click', function() {

      var rowdata = rowSelected('#grid_role');
      var selected = '';

      if ( rowdata.length !== 0) {

        $('#modal_view_capability').modal({backdrop: 'static'});

        // menu page
        call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.page)
        .done(function(data) {
          $('#menu_page_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#menu_page_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });
        
        // menu api
        call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.api)
        .done(function(data) {
          $('#menu_api_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#menu_api_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });

        // admin
        call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.admin)
        .done(function(data) {
          $('#admin_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#admin_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });
        
        // user
        call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.user)
        .done(function(data) {
          $('#user_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#user_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });
        
        // role
        call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.role)
        .done(function(data) {
          $('#role_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#role_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });

        // capability
        call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.capability)
        .done(function(data) {
          $('#capability_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#capability_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });

        // category
        call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.category)
        .done(function(data) {
          $('#category_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#category_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });
        
        // media
        call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.media)
        .done(function(data) {
          $('#media_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#media_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });

         // email
         call_ajax('get', '/api/v1/capability/capability_by_role/' + rowdata[0].id + '/' + category.email)
        .done(function(data) {
          $('#email_capability').html('');
          $.each(data, function(i, v) {
            selected = isChecked(v.selected);
            $('#email_capability').append('<label><input type="checkbox" name="cap_list[]" value='+v.id+' '+selected+'> ' + v.cap_name + '</label><br/>');
          });
        });
      } else {
        alert('Please select row!');
      }
    });

    $('#form_update_capability').submit(function(e) {
      e.preventDefault();
      var rowdata = rowSelected('#grid_role');

      if ( rowdata.length !== 0 ) {
        call_ajax('post', '/api/v1/role/update_capability', {
          role_id: rowdata[0].id,
          cap_id: $('#form_update_capability input[type=checkbox]:checked').serializeArray()
        }).done(function(data) {
          alert(data.message);
        });
      } else {
        alert('Data incorrect!');
      }
    });
    // end
  });
</script>
<?php $this->end() ?>