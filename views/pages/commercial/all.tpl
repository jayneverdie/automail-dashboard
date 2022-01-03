<?php $this->layout('layouts/dashboard', ['title' => 'Commercial All']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Commercial Invoice (Daily report)</h3>
    </div>
    <div class="box-body">

      <!-- grid -->
      <div class="table-responsive">
        <table id="grid_logs" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th>Message</th>
              <th>Date</th>
            </tr>
            <tr>
              <th>Message</th>
              <th>Date</th>
            </tr>
          </thead>
        </table>
      </div>

    </div>
  </div>
</section>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {
    // code here
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
        url: '/automail/commercial/getLogs',
        method: 'post'
      },
      columns: [
        // { data: "ID", type: "int"},
        // { data: 'ProjectName', type: "string"},
        { data: "Message", name: "string"},
        { data: "SendDate", name: "date"}
      ]
    });

  });
</script>
<?php $this->end(); ?>