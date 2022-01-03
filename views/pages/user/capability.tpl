<?php $this->layout('layouts/dashboard', ['title' => 'Capability']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Capability</h3>
    </div>
    <div class="box-body">
      <!-- button -->
      <div class="btn-control">
        <button class="btn btn-primary" id="create"><i class="fa fa-plus" aria-hidden="true"></i> Create</button>
        <button class="btn btn-danger" id="delete"><i class="fa fa-close" aria-hidden="true"></i> Delete</button>
      </div>
      <!-- grid -->
      <table id="grid_capability" class="table table-condensed table-striped" style="width:100%">
        <thead>
          <tr>
            <th>Slug</th>
            <th>Name</th>
            <th>Category</th>
            <th>Status</th>
          </tr>
          <tr>
            <th>Slug</th>
            <th>Name</th>
            <th>Category</th>
            <th>Status</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>

<!-- modal create capability -->
<div class="modal" id="modal_create_capability" tabindex="-1" role="dialog">
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
        <form id="form_create_capability">
          <div class="form-group">
            <label for="capability_name">Name</label>
            <input type="text" name="capability_name" id="capability_name" class="form-control" autocomplete="off" autofocus required>
          </div>
          <div class="form-group">
            <label for="capability_slug">Slug</label>
            <input type="text" name="capability_slug" id="capability_slug" class="form-control" readonly>
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

    var grid_capability_callback = function() {
      // cap name
      $('#grid_capability .--cap-name').editable({
        type: 'text',
        name: 'name',
        url: '/api/v1/capability/update',
        title: 'Name',
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        }
      });

      // category
      call_ajax('get', '/api/v1/category/all_active')
      .done(function(data) {
        // cap status
        $('#grid_capability .--cap-category').editable({
          type: 'select',
          name: 'category',
          url: '/api/v1/capability/update',
          title: 'Category',
          source: pack_dd(data, 'id', 'category_name'),
          success: function(response, newValue) {
            if (response.result === false) {
              alert(response.message);
              window.location.reload();
            }
          }
        });
      });

      // status
      call_ajax('get', '/api/v1/status/all')
      .done(function(data) {
        // cap status
        $('#grid_capability .--cap-status').editable({
          type: 'select',
          name: 'status',
          url: '/api/v1/capability/update',
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
    };

    loadGrid({
      el: '#grid_capability',
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      ajax: {
        url: '/api/v1/capability/all',
        method: 'post'
      },
      fnDrawCallback: grid_capability_callback,
      columns: [
        { data: "cap_slug"},
        { data: "cap_name"},
        { data: 'category_name'},
        { data: "status_name"}
      ],
      columnDefs: [
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--cap-name" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 1
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--cap-category" data-pk="'+row.id+'">'+isNull(data)+'</a>';
          }, targets: 2
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--cap-status" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 3
        }
      ]
    });

    $('#create').on('click', function () {
      $('#modal_create_capability').modal({backdrop: 'static'});
      $('#form_create_capability').trigger('reset');
    });

    $('#delete').on('click', function() {
      var rowdata = rowSelected('#grid_capability');
      if (rowdata.length !== 0) {
        if (confirm('Aru you sure?')) {
          call_ajax('post', '/api/v1/capability/delete', {
            id: rowdata[0].id
          }).done(function(data) {
            if (data.result === true) {
              reloadGrid('#grid_capability', grid_capability_callback);
            } else {
              alert(data.message);
            }
          });
        }
      } else {
        alert('Please select row!');
      }
    }); 

    $('#form_create_capability').submit(function(e) {
      e.preventDefault();
      call_ajax('post', '/api/v1/capability/create', {
        capability_slug: $('#capability_slug').val(),
        capability_name: $('#capability_name').val()
      }).done(function(data) {
        if ( data.result === true ) {
          $('#modal_create_capability').modal('hide');
          $('#form_create_capability').trigger('reset');
          reloadGrid('#grid_capability', grid_capability_callback);
        } else {
          alert(data.message);
        }
      });
    });

    $('#capability_name').keyup(function () {
      $('#capability_slug').val($('#capability_name').val().toLowerCase().replace(/[\s-'.@#\\/+=*%&!$?)({}]/g, "_"));
    });
  });
</script>
<?php $this->end() ?>