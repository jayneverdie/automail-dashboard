<?php $this->layout('layouts/dashboard', ['title' => 'Email Lists']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Email Lists</h3>
      <div class="box-tools pull-right">
        <div class="btn-group">
          <button
            type="button"
            class="btn btn-box-tool dropdown-toggle"
            data-toggle="dropdown"
            aria-expanded="false"
          >
            <div style="font-size: 1.5rem;">
              <i class="fa fal fa-ellipsis-h"></i>
            </div>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li>
              <div style="font-size: 1.5rem;">
                &nbsp;&nbsp;
                <i class="fa fal fa-history"></i>
                <a href="#" class="--view-log" style="font-size: 1.2rem;">
                  View edit history</a
                >
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="box-body">
      <!-- btn -->
      <div class="btn-control">
        <button class="btn btn-primary" id="create_emaillist">
          <i class="fa fa-plus" aria-hidden="true"></i> Create
        </button>
        <button class="btn btn-danger" id="delete_emaillists">
          <i class="fa fa-close" aria-hidden="true"></i> Delete
        </button>
      </div>
      <!-- grid -->
      <table
        id="grid_email_lists"
        class="table table-condensed table-striped"
        style="width:100%"
      >
        <thead>
          <tr>
            <th>ID</th>
            <th>Customer Code</th>
            <th>Email</th>
            <th>Port</th>
            <th>Email Type</th>
            <th>Project Name</th>
            <th>Email Category</th>
            <th>Status</th>
            <th>EmployeeID AX</th>
          </tr>
          <tr>
            <th>ID</th>
            <th>Customer Code</th>
            <th>Email</th>
            <th>Port</th>
            <th>Email Type</th>
            <th>Project Name</th>
            <th>Email Category</th>
            <th>Status</th>
            <th>EmployeeID AX</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>

<!-- modal new emaillist -->
<div class="modal" id="modal_create_emaillist" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button
          type="button"
          class="btn btn-danger pull-right"
          data-dismiss="modal"
          aria-label="Close"
        >
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">Create new emaillist</h4>
      </div>
      <div class="modal-body">
        <!-- Content -->
        <form id="form_new_emaillist">
          <div class="form-group">
            <label for="email_list">Email</label>
            <input
              type="text"
              name="email_list"
              id="email_list"
              class="form-control"
              autofocus
              autocomplete="off"
              required
            />
          </div>
          <div class="form-group">
            <label style="margin-right: 10px;">
              <input type="radio" name="email_type" value="1" checked /> To
            </label>
            <label style="margin-right: 10px;">
              <input type="radio" name="email_type" value="2" /> Cc
            </label>
            <label style="margin-right: 10px;">
              <input type="radio" name="email_type" value="3" /> Bcc
            </label>
            <label style="margin-right: 10px;">
              <input type="radio" name="email_type" value="4" /> Sender
            </label>
            <label style="margin-right: 10px;">
              <input type="radio" name="email_type" value="5" /> Failed
            </label>
          </div>
          <div class="form-group">
            <select
              class="form-control"
              id="select_project"
              name="select_project"
            >
            </select>
          </div>
          <div class="form-group">
            <button class="btn btn-primary" type="submit">
              <i class="fa fa-check" aria-hidden="true"></i>
              Submit
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- modal history -->
<div class="modal" id="modal_history" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button
          type="button"
          class="btn btn-danger pull-right"
          data-dismiss="modal"
          aria-label="Close"
        >
          <span class="glyphicon glyphicon-remove"></span>
        </button>
        <h4 class="modal-title">View edit history</h4>
      </div>
      <div class="modal-body">
        <!-- Content -->
        <div class="form-group">
          <label for="filter_column">Column</label>
          <select id="filter_column" class="form-control"></select>
        </div>
        <hr />
        <!-- grid -->
        <table
          id="grid_history"
          class="table table-condensed table-striped"
          style="width:100%"
        >
          <thead>
            <tr>
              <th>ID</th>
              <th>Value</th>
              <th>By</th>
              <th>Date</th>
              <th>Time</th>
            </tr>
            <tr>
              <th>ID</th>
              <th>Value</th>
              <th>By</th>
              <th>Date</th>
              <th>Time</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function($) {
    // code here
    $(".--view-log").on("click", function() {
      $("#modal_history").modal({ backdrop: "static" });
      $("#grid_history").hide();
      var table = "EmailLists";
      call_ajax("get", "/api/v1/logs/lists/column/" + table).done(function(
        data
      ) {
        $("#filter_column").html("<option value=''>- Select -</option>");
        $.each(data, function(i, v) {
          $("#filter_column").append(
            "<option value='" + v.LogsColumn + "'>" + v.LogsColumn + "</option>"
          );
        });
      });
    });

    $("#filter_column").on("change", function() {
      var column = $("#filter_column").val();

      loadGrid({
        el: "#grid_history",
        processing: true,
        serverSide: true,
        deferRender: true,
        searching: true,
        order: [],
        orderCellsTop: true,
        modeSelect: "single",
        destroy: true,
        ajax: {
          url: "/api/v1/logs/emailLists/" + column,
          method: "post",
        },
        columns: [
          { data: "LogsValueID" },
          { data: "LogsValue" },
          { data: "UserID" },
          { data: "LogsDate" },
          { data: "LogsTime" },
        ],
        columnDefs: [
          {
            render: function(data, type, row) {
              return data;
            },
            targets: 0,
          },
          {
            render: function(data, type, row) {
              return data;
            },
            targets: 1,
          },
          {
            render: function(data, type, row) {
              return data;
            },
            targets: 2,
          },
          {
            render: function(data, type, row) {
              return data;
            },
            targets: 3,
          },
          {
            render: function(data, type, row) {
              return data;
            },
            targets: 4,
          },
        ],
      });

      $("#grid_history").show();
    });

    var grid_callback = function(settings, json) {
      // custmer code
      $("#grid_email_lists .--col-customer-code").editable({
        type: "text",
        name: "customer_code",
        url: "/api/v1/email/update_emailLists",
        title: "Customer Code",
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        },
      });

      // email
      $("#grid_email_lists .--col-email").editable({
        type: "text",
        name: "email",
        url: "/api/v1/email/update_emailLists",
        title: "Email",
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        },
      });

      // port
      $("#grid_email_lists .--col-port").editable({
        type: "text",
        name: "port",
        url: "/api/v1/email/update_emailLists",
        title: "Port",
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        },
      });

      // email type
      call_ajax("get", "/api/v1/email/lists_type").done(function(data) {
        $("#grid_email_lists .--col-email-type").editable({
          type: "select",
          name: "email_type",
          url: "/api/v1/email/update_emailLists",
          title: "EmailType",
          source: pack_dd(data, "ID", "Description"),
          success: function(response, newValue) {
            if (response.result === false) {
              alert(response.message);
              window.location.reload();
            }
          },
        });
      });

      // email project
      call_ajax("get", "/api/v1/email/lists_project").done(function(data) {
        $("#grid_email_lists .--col-project").editable({
          type: "select",
          name: "project_id",
          url: "/api/v1/email/update_emailLists",
          title: "Project",
          source: pack_dd(data, "ProjectID", "ProjectName"),
          success: function(response, newValue) {
            if (response.result === false) {
              alert(response.message);
              window.location.reload();
            }
          },
        });
      });

      // category
      call_ajax("get", "/api/v1/email/lists_category").done(function(data) {
        $("#grid_email_lists .--col-category").editable({
          type: "select",
          name: "email_category",
          url: "/api/v1/email/update_emailLists",
          title: "Email Category",
          source: pack_dd(data, "ID", "Description"),
          success: function(response, newValue) {
            if (response.result === false) {
              alert(response.message);
              window.location.reload();
            }
          },
        });
      });

      // status
      call_ajax("get", "/api/v1/status/all").done(function(data) {
        $("#grid_email_lists .--col-status").editable({
          type: "select",
          name: "status",
          url: "/api/v1/email/update_emailLists",
          title: "Status",
          source: pack_dd(data, "id", "status_name"),
          success: function(response, newValue) {
            if (response.result === false) {
              alert(response.message);
              window.location.reload();
            }
          },
        });
      });

      // emp code
      $("#grid_email_lists .--col-empcode-ax").editable({
        type: "text",
        name: "empcode_ax",
        url: "/api/v1/email/update_emailLists",
        title: "EmployeeID AX",
        success: function(response, newValue) {
          if (response.result === false) {
            alert(response.message);
            window.location.reload();
          }
        },
      });

      // end
    };

    loadGrid({
      el: "#grid_email_lists",
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      destroy: true,
      ajax: {
        url: "/api/v1/email/lists",
        method: "post",
      },
      fnDrawCallback: grid_callback,
      columns: [
        { data: "ID" },
        { data: "CustomerCode" },
        { data: "Email" },
        { data: "Port" },
        { data: "EmailType" },
        { data: "ProjectName" },
        { data: "EmailCategory" },
        { data: "Status" },
        { data: "EmpCode_AX" },
      ],
      columnDefs: [
        {
          render: function(data, type, row) {
            return (
              '<a href="javascript:void(0)" class="--col-customer-code" data-pk="' +
              row.ID +
              '">' +
              isNull(data) +
              "</a>"
            );
          },
          targets: 1,
        },
        {
          render: function(data, type, row) {
            return (
              '<a href="javascript:void(0)" class="--col-email" data-pk="' +
              row.ID +
              '">' +
              isNull(data) +
              "</a>"
            );
          },
          targets: 2,
        },
        {
          render: function(data, type, row) {
            return (
              '<a href="javascript:void(0)" class="--col-port" data-pk="' +
              row.ID +
              '">' +
              isNull(data) +
              "</a>"
            );
          },
          targets: 3,
        },
        {
          render: function(data, type, row) {
            return (
              '<a href="javascript:void(0)" class="--col-email-type" data-pk="' +
              row.ID +
              '">' +
              isNull(data) +
              "</a>"
            );
          },
          targets: 4,
        },
        {
          render: function(data, type, row) {
            return (
              '<a href="javascript:void(0)" class="--col-project" data-pk="' +
              row.ID +
              '">' +
              isNull(data) +
              "</a>"
            );
          },
          targets: 5,
        },
        {
          render: function(data, type, row) {
            return (
              '<a href="javascript:void(0)" class="--col-category" data-pk="' +
              row.ID +
              '">' +
              isNull(data) +
              "</a>"
            );
          },
          targets: 6,
        },
        {
          render: function(data, type, row) {
            return (
              '<a href="javascript:void(0)" class="--col-status" data-pk="' +
              row.ID +
              '">' +
              isNull(data) +
              "</a>"
            );
          },
          targets: 7,
        },
        {
          render: function(data, type, row) {
            return (
              '<a href="javascript:void(0)" class="--col-empcode-ax" data-pk="' +
              row.ID +
              '">' +
              isNull(data) +
              "</a>"
            );
          },
          targets: 8,
        }
      ],
    });

    $("#create_emaillist").on("click", function() {
      $("#modal_create_emaillist").modal({ backdrop: "static" });
      $("#form_new_emaillist").trigger("reset");

      // lists_project
      call_ajax("get", "/api/v1/email/lists_project").done(function(data) {
        if (data.length > 0) {
          $("#select_project").html("เลือก Project");
          $.each(data, function(i, v) {
            $("#select_project").append(
              "<option value='" +
                v.ProjectID +
                "'>" +
                v.ProjectName +
                "</option>"
            );
          });
        }
      });
    });

    $("#form_new_emaillist").submit(function(e) {
      e.preventDefault();
      // if (confirm("Are you sure ?")) {
      call_ajax("post", "/api/v1/email/create_lists", {
        email_list: $("#email_list").val(),
        email_type: $("input[name=email_type]:checked").val(),
        project: $("#select_project").val(),
      }).done(function(data) {
        if (data.result === true) {
          $("#modal_create_emaillist").modal("hide");
          $("#form_new_emaillist").trigger("reset");
          reloadGrid("#grid_email_lists", grid_callback);
        } else {
          alert(data.message);
        }
      });
      // }
    });

    $("#delete_emaillists").on("click", function() {
      var rowdata = rowSelected("#grid_email_lists");
      if (rowdata.length !== 0) {
        if (confirm("Aru you sure?")) {
          call_ajax("post", "/api/v1/email/delete_lists", {
            id: rowdata[0].ID,
          }).done(function(data) {
            if (data.result === true) {
              reloadGrid("#grid_email_lists", grid_callback);
            } else {
              alert(data.message);
            }
          });
        }
      } else {
        alert("Please select row!");
      }
    });
  });
</script>
<?php $this->end(); ?>
