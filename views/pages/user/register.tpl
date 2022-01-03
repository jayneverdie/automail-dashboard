<?php $this->layout('layouts/dashboard', ['title' => 'Register']);?>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Register</h3>
    </div>
    <div class="box-body">
			<?php echo getFlashMessage(); ?>
			<form action="/user/register/save" method="post">

				<div class="form-group">
					<label for="empid">Employee ID</label>
					<input type="text" name="empid" class="form-control" 
						id="empid" 
						autofocus
						autocomplete="off" 
						required
						placeholder="รหัสพนักงาน"
						style="max-width: 300px;" />
					
				</div>

				<div class="form-group">
					<p id="fullname"></p>
				</div>

				<?php if(userCan('select_employee')) : ?>
				<div class="form-group">
					<button type="button" id="select_empid" class="btn btn-info">Select Employee ID</button>
				</div>
				<?php endif; ?>
				
				<div class="form-group">
					<label for="username">Username</label>
					<input type="text" name="username" class="form-control" 
						id="username" 
						autocomplete="off" 
						required
						readonly
						placeholder="ชื่อผู้ใช้งาน"
						style="max-width: 300px;" />
				</div>

				<div class="form-group">
					<label for="email">Email</label>
					<input type="email" name="email" class="form-control" 
						id="email" 
						autocomplete="off"
						required
						placeholder="อีเมล"
						style="max-width: 300px;" />
				</div>

				<div class="form-group">
					<label for="firstname">Firstname</label>
					<input type="text" name="firstname" class="form-control" 
						id="firstname" 
						autocomplete="off"
						required
						placeholder="ชื่อ"
						style="max-width: 300px;" />
				</div>

				<div class="form-group">
					<label for="lastname">Lastname</label>
					<input type="text" name="lastname" class="form-control" 
						id="lastname" 
						autocomplete="off"
						required
						placeholder="นามสกุล"
						style="max-width: 300px;" />
				</div>

				<div class="form-group">
					<label for="password">Password</label>
					<input type="password" name="password" class="form-control" 
						id="password" 
						autocomplete="off"
						required
						placeholder="รหัสผ่าน"
						style="max-width: 300px;" />
				</div>

				<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $key['csrf_name']; ?>">
  			<input type="hidden" name="<?php echo $value ?>" value="<?php echo $key['csrf_value']; ?>">
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
    </div>
  </div>
</section>

<!-- Modal -->
<div class="modal" id="modal_select_empid" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close">
					<span class="glyphicon glyphicon-remove"></span>
				</button>
				<h4 class="modal-title">Header</h4>
			</div>
			<div class="modal-body">
				<!-- Content -->
				<table id="grid_employee" class="table table-condensed table-striped" style="width:100%">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Username</th>
                <th>Sername</th>
							</tr>
							<tr>
								<th>Employee</th>
								<th>Username</th>
								<th>Sername</th>
							</tr>
            </thead>
          </table>
			</div>
		</div>
	</div>
</div>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {
		// code here
		
		$('#select_empid').on('click', function() {
			$('#modal_select_empid').modal({backdrop: 'static'});

			loadGrid({
				el: '#grid_employee',
				processing: true,
				serverSide: true,
				deferRender: true,
				searching: true,
				order: [],
				orderCellsTop: true,
				destroy: true,
				modeSelect: "single",
				ajax: {
					url: '/api/v1/employee/all',
					method: 'post'
				},
				// fnDrawCallback: grid_user_callback,
				columns: [
					{ data: "CODEMPID"},
					{ data: 'EMPNAME'},
					{ data: "EMPLASTNAME"}
				]
			});
		});
		
		$('#grid_employee').on('dblclick', function() {
			var rowdata = rowSelected('#grid_employee');
			if (rowdata.length !== 0) {
				// do
				$("#empid").val(rowdata[0].CODEMPID);
				$('#fullname').text(rowdata[0].EMPNAME+ ' ' + rowdata[0].EMPLASTNAME);
				$("#username").val(rowdata[0].CODEMPID);
				$('#modal_select_empid').modal('hide');
				$('#email').val('').focus();
			}
		});

		$('#empid').keyup(function(e) {
			$("#username").val($('#empid').val().replace(' ', ''));
		});
		// end
	});
</script>
<?php $this->end(); ?>