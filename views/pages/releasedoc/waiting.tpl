<?php $this->layout('layouts/dashboard', ['title' => 'Release-Doc All']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Release original document (waiting to be sent)</h3>
    </div>
    <div class="box-body">

      <!-- grid -->
      <div class="table-responsive">
        <table id="grid_logs" class="table table-condensed table-striped" style="width:100%">
          <thead>
            <tr>
              <th>So</th>
              <th>Release original document</th>
              <th>CreateDate</th>
            </tr>
            <tr>
              <th>So</th>
              <th>Release original document</th>
              <th>CreateDate</th>
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
        url: '/automail/releasedoc/getWaiting',
        method: 'post'
      },
      columns: [
        { data: "So", name: "string"},
        { data: 'Release', type: "string"},
        { data: 'CreateDate', type: "string"}
      ]
    });

  });
</script>
<?php $this->end(); ?>