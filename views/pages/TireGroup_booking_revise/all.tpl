<?php $this->layout('layouts/dashboard', ['title' => 'TireGroup Booking Revise All']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">TireGroup Booking Revise All Logs</h3>
    </div>
    <div class="box-body">
      <table id="booking_revise_all_logs" class="table table-condensed table-striped" style="width:100%">
        <thead>
          <tr>
            <th>Message</th>
            <th>Customer</th>
            <th>File name</th>
            <th>Date</th>
          </tr>
          <tr>
            <th>Message</th>
            <th>Customer</th>
            <th>File name</th>
            <th>Date</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {

    loadGrid({
			el: '#booking_revise_all_logs',
			processing: true,
			serverSide: true,
			deferRender: true,
			searching: true,
			order: [],
			orderCellsTop: true,
			modeSelect: "single",
			destroy: true,
			ajax: {
				url: '/api/v1/automail/TireGroup_booking_revise/all/logs',
				method: 'post'
			},
			columns: [
				{ data: "Message" },
				{ data: 'CustomerCode'},
				{ data: 'FileName'},
				{ data: 'SendDate'}
      ],
    });
  });
</script>
<?php $this->end(); ?>
