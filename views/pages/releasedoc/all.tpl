<?php $this->layout('layouts/dashboard', ['title' => 'Release-Doc All']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Release original document</h3>
    </div>
    <div class="box-body">

      <!-- grid -->
      <div class="table-responsive">
        <table id="grid_logs" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th>Message</th>
              <th>Customer</th>
              <th>SO</th>
              <th>Invoice</th>
              <th>Date</th>
            </tr>
            <tr>
              <th>Message</th>
              <th>Customer</th>
              <th>SO</th>
              <th>Invoice</th>
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
        url: '/automail/releasedoc/getLogs',
        method: 'post'
      },
      columns: [
        { data: "Message", name: "string"},
        { data: 'CustomerCode', type: "string"},
        { data: 'SO', type: "string"},
        { data: 'Invoice', type: "string"},
        { data: "SendDate", name: "date"}
      ]
    });

  });
</script>
<?php $this->end(); ?>