<?php $this->layout('layouts/dashboard', ['title' => 'Port Upload']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Port Upload</h3> 
    </div>
    <div class="box-body">
      <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
          <a
            href="#upload_port"
            aria-controls="upload_port"
            role="tab"
            data-toggle="tab"
            >Upload ข้อมูล Port ใหม่</a
          >
        </li>
        <li role="presentation">
          <a
            href="#view_current"
            aria-controls="view_current"
            role="tab"
            data-toggle="tab"
            >ข้อมูล Port ล่าสุด</a
          >
        </li>
        <li role="presentation">
          <a
            href="#export_port"
            aria-controls="export_port"
            role="tab"
            data-toggle="tab"
            >Export ข้อมูล Port</a
          >
        </li>
      </ul>

      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="upload_port">
          <form
            id="formUploadPort"
            action="/api/v1/port/upload"
            enctype="multipart/form-data"
            method="post"
            style="max-width: 300px"
          >
            <h3>Update รายการ Port</h3>
            <div class="form-group" style="margin: 40px 0px;">
              <input type="file" name="port_files" required />
            </div>
            <div class="form-group">
              <label for="">เลือก Project</label>
              <select name="project" id="project" class="form-control" required>
                <option value="">--เลือก--</option>
              </select>
            </div>
            <div class="form-group" style="margin: 40px 0px;">
              <button type="submit" class="btn btn-lg btn-success">
                อัพโหลด!
              </button>
            </div>
          </form>
          <hr />

          <h1 style="margin-bottom: 20px;">
            ตัวอย่าง Template
          </h1>
          <img src="/files/port/image/demo.png" alt="" width="100%" />
        </div>
        <div role="tabpanel" class="tab-pane" id="view_current">
          <div id="view_current" style="margin: 20px 0px">
            <div class="table-responsive">
              <table
                id="grid_port"
                class="table table-condensed table-striped"
                style="width:100%"
              >
                <thead>
                  <tr>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Port</th>
                    <th>Email Type</th>
                    <th>Project Name</th>
                    <th>Email Category</th>
                  </tr>
                  <tr>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Port</th>
                    <th>Email Type</th>
                    <th>Project Name</th>
                    <th>Email Category</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="export_port">
          <form
            action="/api/v1/port/export_port"
            method="post"
            style="margin: 20px 0px;"
          >
            <div style="margin: 20px 0px; max-width: 300px">
              <label for="">เลือก Project</label>
              <select
                name="project_export"
                id="project_export"
                class="form-control"
                required
              >
                <option value="">--เลือก--</option>
              </select>
            </div>
            <button type="submit" class="btn btn-lg btn-success">
              Download รายการ Port ปัจจุบัน
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function($) {
    // code here
    // var project_id = '33';
    call_ajax("post", "/api/v1/port/upload_project").done(function(data) {
      $.each(data, function(i, v) {
        $("#project").append(
          "<option value='" + v.ProjectID + "'>" + v.ProjectName + "</option>"
        );

        $("#project_export").append(
          "<option value='" + v.ProjectID + "'>" + v.ProjectName + "</option>"
        );
      });
    });

    // call_ajax('get', '/api/v1/port/upload_project')
    // .done(function(data) {
    //   $('#project').html("<option value=''>- Select -</option>");
    //   $.each(data, function(i, v) {
    //     $('#project').append("<option value='" + v.ProjectID + "'>" + v.ProjectName + "</option>");
    //   });
    // });

    loadGrid({
      el: "#grid_port",
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      ajax: {
        url: "/api/v1/port/port_all",
        method: "post",
      },
      columns: [
        { data: "Email", name: "string" },
        { data: "Country", name: "string" },
        { data: "Port", name: "string" },
        { data: "EmailType", name: "string" },
        { data: "ProjectName", name: "string" },
        { data: "EmailCategory", name: "string" },
      ],
    });
  });
</script>
<?php $this->end(); ?>
