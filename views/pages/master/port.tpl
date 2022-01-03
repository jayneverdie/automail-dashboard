<?php 
$this->layout('layouts/dashboard', ['title' => 'Port']);
use App\Port\PortAPI;

$portcamso = (new PortAPI)->getEmailListsPortCamso();
$porttiregroup = (new PortAPI)->getEmailListsPortTiregroup();
?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Port</h3>
    </div>
    <div class="box-body">
      <!-- Button -->
      <div class="btn-control">
        <button class="btn btn-default" id="view_type"><i class="fa fa-eye" aria-hidden="true"></i> View capability</button>
      </div>
      <!-- Grid -->
      <table id="grid_project" class="table table-condensed table-striped" style="width:100%">
        <thead>
          <tr>
            <th width="20%">ID</th>
            <th>ProjectName</th>
          </tr>
          <tr>
            <th>ID</th>
            <th>ProjectName</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>

<!-- modal view customerport -->
<div class="modal" id="modal_view_type" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">CustomerPort</h4>
      </div>
      <div class="modal-body">
        <!-- Content -->
        <form id="form_customer_port">
          
          <div class="nav-tabs-custom" id="customerviewCamso">
            <ul class="nav nav-tabs" id="TabViewCamso">

              <?php 
                foreach ($portcamso as $key => $value) { 
              ?>
                <?php if ($key==0){ ?>
                  <li class="active"><a href="#<?php echo $value['ProjectID']; ?>" data-toggle="tab" aria-expanded="false"><?php echo $value['ProjectName']; ?></a></li>   
                <?php }else{ ?>
                  <li><a href="#<?php echo $value['ProjectID']; ?>" data-toggle="tab" aria-expanded="false"><?php echo $value['ProjectName']; ?></a></li>              
                <?php } ?>
              <?php
                }
              ?>

            </ul> 
            <div class="tab-content">

              <div class="input-group" style="width: 350px;">
                <select id="InputPortCamso" name='InputPortCamso' class="form-control"></select>
              </div>

            </div>
          </div>

          <div class="nav-tabs-custom" id="customerviewTiregroup">
            <ul class="nav nav-tabs" id="TabViewTiregroup">

              <?php 
                foreach ($porttiregroup as $key => $value) { 
              ?>
                <?php if ($key==0){ ?>
                  <li class="active"><a href="#<?php echo $value['ProjectID']; ?>" data-toggle="tab" aria-expanded="false"><?php echo $value['ProjectName']; ?></a></li>   
                <?php }else{ ?>
                  <li><a href="#<?php echo $value['ProjectID']; ?>" data-toggle="tab" aria-expanded="false"><?php echo $value['ProjectName']; ?></a></li>              
                <?php } ?>
              <?php
                }
              ?>

            </ul> 
            <div class="tab-content">

              <div class="input-group" style="width: 350px;">
                <select id="InputPortTiregroup" name='InputPortTiregroup' class="form-control"></select>
              </div>

            </div>
          </div>

          <button type="submit" class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i> View Email</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- modal view grid email -->
<div class="modal" id="modal_view_email" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">Email</h4>
      </div>
      <div class="modal-body">
        <!-- Button -->
        <div class="btn-control">
          <button class="btn btn-primary" id="create"><i class="fa fa-plus" aria-hidden="true"></i> Create</button>
          <button class="btn btn-danger" id="delete"><i class="fa fa-close" aria-hidden="true"></i> Delete</button>
        </div>
      <!-- Grid -->
        <input type="hidden" id="InputCustomerPort" class="form-control">
        <!-- Grid -->
        <table id="grid_email" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th width="20%">Email</th>
              <th>Port</th>
              <th>EmailType</th>
              <th>EmailCategory</th>
            </tr>
            <tr>
              <th>Email</th>
              <th>Port</th>
              <th>EmailTypeName</th>
              <th>EmailCategoryName</th>
            </tr>
          </thead>
        </table>

      </div>
    </div>
  </div>
</div>

<!-- modal create email -->
<div class="modal" id="modal_create_email" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">Create Email</h4>
      </div>
      <div class="modal-body">
        <form id="form_create_email">
          <div class="form-group">
            <label for="email_name">Email</label>
            <input type="text" name="email_name" id="email_name" class="form-control" autocomplete="off" autofocus required>
          </div>
          <div class="select-group">
            <label for="email_type">Email Type</label>
            <select id="email_type" name='email_type' class="form-control"></select>
          </div>
          <div class="select-group">
            <label for="email_category">Email Category</label>
            <select id="email_category" name='email_category' class="form-control"></select>
          </div>
            <br>
            <input type="hidden" name="project_id" id="project_id" class="form-control" autocomplete="off" required>
            <input type="hidden" name="port_name" id="port_name" class="form-control" autocomplete="off" required>
          <button class="btn btn-primary" type="submit"><i class="fa fa-check" aria-hidden="true"></i> Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $this->push('scripts') ?>
<script>

  jQuery(document).ready(function ($) {
    
    // var grid_project_callback = function() {
    //   // email name
    //   $('#grid_project .--project-name').editable({
    //     type: 'text',
    //     name: 'name',
    //     url: '/api/v1/port/update',
    //     title: 'Email',
    //     success: function(response, newValue) {
    //       if (response.result === false) {
    //         alert(response.message);
    //         window.location.reload();
    //       }
    //     }
    //   });
    // };

    loadGrid({
      el: '#grid_project',
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      ajax: {
        url: '/api/v1/port/all',
        method: 'post'
      },
      // fnDrawCallback: grid_project_callback,
      columns: [
        { data: "ID"},
        { data: 'ProjectName'}
      ],
      columnDefs: [
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--id" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 0
        },
        {
          render: function(data, type, row) {
            return '<a href="javascript:void(0)" class="--project-name" data-pk="'+row.id+'">'+data+'</a>';
          }, targets: 1
        }
      ]
    });
    
    // view type
    $('#view_type').on('click', function() {

      var rowdata = rowSelected('#grid_project');
      // var selected;
      if ( rowdata.length !== 0) {

        changeview = rowdata[0].ID;

        if (rowdata[0].ID==1) {
          
          $('#customerviewCamso').show();
          $('#customerviewTiregroup').hide();

          $('#modal_view_type').modal({backdrop: 'static'});

          var customerport = $('#TabViewCamso li.active').find("a").html();

          call_ajax('get', '/api/v1/port/load/type/' + customerport)
          .done(function(data) {
            $('#InputPortCamso').html("<option value=''>- Select -</option>");
            $.each(data, function(i, v) {
              $('#InputPortCamso').append("<option value='" + v.Port + "'>" + v.Port + "</option>");
            });
          });

          project_id = $('ul.nav-tabs > li.active > a').attr("href").replace("#", "");

          $("ul.nav-tabs > li > a").click(function() {
            project_id = $(this).attr("href").replace("#", "");
          });

          $("#TabViewCamso li").click(function() {
            if (!$(this).hasClass("active")) {
              $("#TabViewCamso li.active").removeClass("active")
              $(this).addClass("active")
              // console.log($(this).find("a").html())
              
              var customerport = $(this).find("a").html();
              call_ajax('get', '/api/v1/port/load/type/' + customerport)
              .done(function(data) {
                $('#InputPortCamso').html("<option value=''>- Select -</option>");
                $.each(data, function(i, v) {
                  $('#InputPortCamso').append("<option value='" + v.Port + "'>" + v.Port + "</option>");
                });
              });
            }
          });

        }else{

          $('#customerviewCamso').hide();
          $('#customerviewTiregroup').show();

          $('#modal_view_type').modal({backdrop: 'static'});

          var customerport = $('#TabViewTiregroup li.active').find("a").html();

          call_ajax('get', '/api/v1/port/load/type/' + customerport)
          .done(function(data) {
            $('#InputPortTiregroup').html("<option value=''>- Select -</option>");
            $.each(data, function(i, v) {
              $('#InputPortTiregroup').append("<option value='" + v.Port + "'>" + v.Port + "</option>");
            });
          });

          project_id = $('ul.nav-tabs > li.active > a').attr("href").replace("#", "");

          $("ul.nav-tabs > li > a").click(function() {
            project_id = $(this).attr("href").replace("#", "");
          });

          $("#TabViewTiregroup li").click(function() {
            if (!$(this).hasClass("active")) {
              $("#TabViewTiregroup li.active").removeClass("active")
              $(this).addClass("active")
              // console.log($(this).find("a").html())

              var customerport = $(this).find("a").html();
              call_ajax('get', '/api/v1/port/load/type/' + customerport)
              .done(function(data) {
                $('#InputPortTiregroup').html("<option value=''>- Select -</option>");
                $.each(data, function(i, v) {
                  $('#InputPortTiregroup').append("<option value='" + v.Port + "'>" + v.Port + "</option>");
                });
              });
            }
          });

        }

      } else {
        alert('Please select row!');
      }

    });

    $('#form_customer_port').submit(function(e) {
      e.preventDefault();

      $('#modal_view_email').modal({backdrop: 'static'});
      
      var grid_email_callback = function() {
        // email name
        $.fn.editable.defaults.mode = 'inline';
        $('#grid_email .--email-name').editable({
          type: 'text',
          name: 'name',
          url: '/api/v1/port/update',
          title: 'Email',
          success: function(response, newValue) {
            if (response.result === false) {
              // alert(response.message);
              reloadGrid('#grid_email',true);
              // window.location.reload();
            }
          }
        });

        // type
        call_ajax('get', '/api/v1/email/lists_type')
        .done(function(data) {
          // cap status
          $('#grid_email .--type-name').editable({
            type: 'select',
            name: 'type',
            url: '/api/v1/port/update',
            title: 'EmailType',
            source: pack_dd(data, 'ID', 'Description'),
            success: function(response, newValue) {
              if (response.result === false) {
                reloadGrid('#grid_email',true);
              }
            }
          });
        });

        // category
        call_ajax('get', '/api/v1/port/load/category/'+project_id)
        .done(function(data) {
          // cap status
          $('#grid_email .--category-name').editable({
            type: 'select',
            name: 'category',
            url: '/api/v1/port/update',
            title: 'EmailCategory',
            source: pack_dd(data, 'ID', 'Description'),
            success: function(response, newValue) {
              if (response.result === false) {
                reloadGrid('#grid_email',true);
              }
            }
          });
        });

      };

      if (changeview==1) {
        $('#InputCustomerPort').val($('#InputPortCamso').val());
      }else{
        $('#InputCustomerPort').val($('#InputPortTiregroup').val());
      }

      $('#port_name').val($('#InputCustomerPort').val());

      var port = $('#InputCustomerPort').val();

      loadGrid({
        el: '#grid_email',
        processing: true,
        serverSide: true,
        deferRender: true,
        searching: true,
        order: [],
        orderCellsTop: true,
        destroy: true,
        modeSelect: "single",
        ajax: {
          url: '/api/v1/port/load/email',
          method: 'post',
          data : {
            port : port
          }
        },
        fnDrawCallback: grid_email_callback,
        columns: [
          { data: "Email"},
          { data: 'Port'},
          { data: 'EmailTypeName'},
          { data: 'EmailCategoryName'}
        ],
        columnDefs: [
          {
            render: function(data, type, row) {
              return '<a href="javascript:void(0)" class="--email-name" data-pk="'+row.ID+'">'+data+'</a>';
            }, targets: 0
          },
          {
            render: function(data, type, row) {
              return '<a href="javascript:void(0)" class="--port-name" data-pk="'+row.ID+'">'+data+'</a>';
            }, targets: 1
          },
          {
            render: function(data, type, row) {
              return '<a href="javascript:void(0)" class="--type-name" data-pk="'+row.ID+'">'+data+'</a>';
            }, targets: 2
          },
          {
            render: function(data, type, row) {
              return '<a href="javascript:void(0)" class="--category-name" data-pk="'+row.ID+'">'+data+'</a>';
            }, targets: 3
          }
        ]
      });

    });

    $('#create').on('click', function() {

      if ( $('#port_name').val().length === 0) {
        alert('Please Select Port');
      }else{
        
        $('#modal_create_email').modal({backdrop: 'static'});
      
        call_ajax('get', '/api/v1/email/lists_type')
        .done(function(data) {
          $('#email_type').html("<option value=''>- Select -</option>");
          $.each(data, function(i, v) {
            $('#email_type').append("<option value='" + v.ID + "'>" + v.Description + "</option>");
          });
        });

        call_ajax('get', '/api/v1/port/load/category/'+project_id)
        .done(function(data) {
          $('#email_category').html("<option value=''>- Select -</option>");
          $.each(data, function(i, v) {
            $('#email_category').append("<option value='" + v.ID + "'>" + v.Description + "</option>");
          });
        });

      }
      
      $('#project_id').val(project_id);

    });

    $('#form_create_email').submit(function(e) {
      e.preventDefault();
      
      call_ajax('post', '/api/v1/port/create', {
        email_name : $('#email_name').val(),
        email_type : $('#email_type').val(),
        email_category : $('#email_category').val(),
        project_id : $('#project_id').val(),
        port_name : $('#port_name').val()
      }).done(function(data) {
        if (data.result === true) {
          $('#modal_create_email').modal('hide');
          $('#form_create_email').trigger('reset');
          reloadGrid('#grid_email',true);
          alert(data.message);
        } else {
          alert(data.message);
        }
      });

    });

    $('#delete').on('click', function() {
      var rowdata = rowSelected('#grid_email');
      if (rowdata.length !== 0) {
        if (confirm('Aru you sure?')) {
          call_ajax('post', '/api/v1/port/delete', {
            id: rowdata[0].ID
          }).done(function(data) {
            if (data.result === true) {
              reloadGrid('#grid_email',true);
            } else {
              alert(data.message);
            }
          });
        }
      } else {
        alert('Please select row!');
      }
    }); 

  });

</script>
<?php $this->end() ?>