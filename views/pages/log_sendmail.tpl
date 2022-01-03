<?php $this->layout('layouts/dashboard', ['title' => 'Log Sendmail']);?>

<!-- <section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Log Sendmail</h3>
    </div>
    <div class="box-body">
      <div class="table-responsive">
        <table id="grid_log_sendmail" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th>EmailName</th>
              <th>EmailType</th>
              <th>ProjectName</th>
              <th>Send Date</th>
            </tr>
            <tr>
              <th>Email</th>
              <th>EmailType</th>
              <th>ProjectName</th>
              <th>Send Date</th>
            </tr>
          </thead>
				</table>
			</div>
    </div>
  </div>
</section> -->

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Logs All</h3>
    </div>
    <div class="box-body">
      <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
          <a
            href="#view_logs"
            aria-controls="view_logs"
            role="tab"
            data-toggle="tab"
            >Logs All</a
          >
        </li>
        <li role="presentation">
          <a
            href="#view_logs_send"
            aria-controls="view_logs_send"
            role="tab"
            data-toggle="tab"
            >Logs Sendmail</a
          >
        </li>
      </ul>

      <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="view_logs">
          <div id="view_logs" style="margin: 20px 0px">
            <div class="table-responsive">
              <table
                id="grid_logs"
                class="table table-condensed table-striped"
                style="width:100%"
              >
                <thead>
                  <tr>
                    <th>ProjectName</th>
                    <th>Message</th>
                    <th>FileName</th>
                    <th>Invoice</th>
                    <th>Send Date</th>
                  </tr>
                  <tr>
                    <th>ProjectName</th>
                    <th>Message</th>
                    <th>FileName</th>
                    <th>Invoice</th>
                    <th>Send Date</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="view_logs_send">
          <div id="view_logs_send" style="margin: 20px 0px">
            <div class="table-responsive">
              <table
                id="grid_log_sendmail"
                class="table table-condensed table-striped"
                style="width:100%"
              >
                <thead>
                  <tr>
                    <th>EmailName</th>
                    <th>EmailType</th>
                    <th>ProjectName</th>
                    <th>Send Date</th>
                  </tr>
                  <tr>
                    <th>Email</th>
                    <th>EmailType</th>
                    <th>ProjectName</th>
                    <th>Send Date</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
       
      </div>
    </div>
  </div>
</section>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {

    loadGrid({
      el: '#grid_logs',
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      ajax: {
        url : '/api/v1/logs/all',
        method: 'post'
      },
      // fnDrawCallback: grid_log_sendmail_callback,
      columns: [
        { data: "ProjectName" },
        { data: 'Message'},
        { data: 'FileName'},
        { data: 'Invoice'},
        { data: 'SendDate'}
      ]
    });

    loadGrid({
      el: '#grid_log_sendmail',
      processing: true,
      serverSide: true,
      deferRender: true,
      searching: true,
      order: [],
      orderCellsTop: true,
      modeSelect: "single",
      ajax: {
        url : '/api/v1/logs/all_logsenmail',
        method: 'post'
      },
      // fnDrawCallback: grid_log_sendmail_callback,
      columns: [
        { data: "Email" },
        { data: 'EmailType'},
        { data: 'ProjectName'},
        { data: 'SendDate'}
      ]
    });

  });
</script>
<?php $this->end(); ?>