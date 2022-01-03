<?php $this->layout('layouts/dashboard', ['title' => 'Menu Page']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Menu Page</h3>
    </div>
    <div class="box-body">
      <!-- Button -->
      <div class="btn-control">
        <button type="button" id="create_menu" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> Create</button>
        <button type="button" id="delete_menu" class="btn btn-danger"><i class="fa fa-close" aria-hidden="true"></i> Delete</button>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table id="grid_menu" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Link</th>
              <th>Name</th>
              <th>Position</th>
              <th>Parent</th>
              <th>Order</th>
              <th>Capability</th>
              <th>Category</th>
              <th>Status</th>
            </tr>
            <tr>
              <th>ID</th>
              <th>Link</th>
              <th>Name</th>
              <th>Position</th>
              <th>Parent</th>
              <th>Order</th>
              <th>Capability</th>
              <th>Category</th>
              <th>Status</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- Modal -->
<div class="modal" id="modal_menu" tabindex="-1" role="dialog">
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
        <form id="form_menu">
          <div class="form-group">
            <label for="menu_link">Link</label>
            <input type="text" name="menu_link" class="form-control" autofocus autocomplete="off" required>
          </div>
          <div class="form-group">
            <label for="menu_name">Name</label>
            <input type="text" name="menu_name" class="form-control" autocomplete="off" required>
          </div>
          <input type="hidden" name="menu_category" value="page">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check" aria-hidden="true"></i> Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $this->push('scripts') ?>
<script>
  jQuery(document).ready(function ($) {

    var grid_menu_callback = function(settings, json) {
      // link
      $('#grid_menu .--menu-link').editable({
        type: 'text',
        name: 'link',
        url: '/api/v1/menu/update',
        title: 'Link',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
      });

      // name
      $('#grid_menu .--menu-name').editable({
        type: 'text',
        name: 'name',
        url: '/api/v1/menu/update',
        title: 'Name',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
      });

      // position
      $('#grid_menu .--menu-position').editable({
        type: 'text',
        name: 'position',
        url: '/api/v1/menu/update',
        title: 'Position',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
      });

      // parent
      $('#grid_menu .--menu-parent').editable({
        type: 'text',
        name: 'parent',
        url: '/api/v1/menu/update',
        title: 'Parent',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
      });

      // order
      $('#grid_menu .--menu-order').editable({
        type: 'text',
        name: 'order',
        url: '/api/v1/menu/update',
        title: 'Order',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
      });

      // capability
      call_ajax('get', '/api/v1/capability/all_active')
      .done(function(data) {
        // cap
        $('#grid_menu .--menu-capability').editable({
          type: 'select',
          name: 'capability',
          url: '/api/v1/menu/update',
          title: 'Capability',
          source: pack_dd(data, 'id', 'cap_name', true),
          success: function(response, newValue) {
            if (response.result === false) {
              alert(response.message);
              window.location.reload();
            }
          }
        });
      });

      call_ajax('get', '/api/v1/category/all_active')
      .done(function(data) {
        // category
        $('#grid_menu .--menu-category').editable({
          type: 'select',
          name: 'category',
          url: '/api/v1/menu/update',
          title: 'Status',
          source: pack_dd(data, 'id', 'category_name'),
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
        $('#grid_menu .--menu-status').editable({
          type: 'select',
          name: 'status',
          url: '/api/v1/menu/update',
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
    }

    loadGrid({
      el: '#grid_menu',
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      ajax: {
        url: '/api/v1/menu/all_page',
        method: 'post'
      },
      fnDrawCallback: grid_menu_callback,
      columns: [
        { data: "id", width: 50 },
        { data: "menu_link" },
        { data: "menu_name" },
        { data: "menu_position", width: 50 },
        { data: "menu_parent", width: 50 },
        { data: "menu_order", width: 50 },
        { data: "cap_name" },
        { data: 'category_name', width: 50},
        { data: "status_name", width: 80 }
      ],
      columnDefs: [
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--menu-link" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 1
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--menu-name" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 2
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--menu-position" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 3
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--menu-parent" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 4
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--menu-order" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 5
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--menu-capability" data-pk="'+row.id+'">'+isNull(data)+'</a>';
          }, targets: 6
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--menu-category" data-pk="'+row.id+'">'+isNull(data)+'</a>';
          }, targets: 7
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--menu-status" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 8
        },
      ]
    });

    $('#create_menu').on('click', function () {
      $('#modal_menu').modal({backdrop: 'static'});
      $('#form_menu').trigger('reset');
    });

    $('#delete_menu').on('click', function () {
      var rowdata = rowSelected('#grid_menu');
      if (rowdata.length !== 0) {
        if (confirm('Are you sure ?')) {
          call_ajax('post', '/api/v1/menu/delete', {
            id: rowdata[0].id
          }).done(function (data) {
            if (data.result === true) {
              reloadGrid('#grid_menu', grid_menu_callback);
            } else {
              alert(data.message);
            }
          });
        }
      } else  {
        alert('Please select row!');
      }
    });
    
    $('#form_menu').submit(function(e) {
      e.preventDefault();
      call_ajax('post', '/api/v1/menu/create', {
        menu_link: $('input[name=menu_link]').val(),
        menu_name: $('input[name=menu_name]').val(),
        menu_category: $('input[name=menu_category]').val()
      }).done(function (data) {
        if (data.result === true) {
          $('#form_menu').trigger('reset');
          $('#modal_menu').modal('hide');
          reloadGrid('#grid_menu', grid_menu_callback);
        } else {
          $('#form_menu').trigger('reset');
          alert(data.message);
        }
      });
    });
  });

</script>
<?php $this->end() ?>