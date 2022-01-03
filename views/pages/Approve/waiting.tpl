<?php $this->layout('layouts/clean', ['title' => 'Approve Lists']);?>

<?php 
	use App\Kpi\KpiAPI;
	$userid = $_GET["userid"];
	$nonce = $_GET["nonce"];
	$today = date('Y-m-d');
	$data = (new KpiAPI)->getSoConfirm($today,1,$userid);
	$getnonce = (new KpiAPI)->checknonce($nonce);
?>

<style type="text/css">
	table {
	    display: block;
	    overflow-x: auto;
	    white-space: nowrap;
	}
	/*.search-table-outter { 
		overflow-x: scroll; 
	}*/
	input[type=checkbox] {
	    width: 20px;
	    height: 20px;
	}
</style>

<section class="content">

	<?php if($getnonce['status']==404){?>

	<div class="box box-danger">
		<div class="box-body">
			<h3 class="box-title" align="center"><?php echo $getnonce['message']; ?> </h3>
		</div>
	</div>

	<?php }else{ ?>

  	<div class="box box-primary">
		<div class="box-body">

			<h3 class="box-title">SO Confirmation </h3>
			<h3 class="box-title">Status : Waiting </h3>

			<form id="form_approve" onsubmit="return submit_approve()">
				<div class="search-table-outter wrapper">
				<table class="table table-condensed table-striped table-hover" style="font-size: 14px;">
			 		<tr class="info">
			 			<th valign="top" class="warning"><label><u>APPROVE</u></label></th>
			 			<th valign="top" class="danger"><label><u>REJECT</u></label></th>
			 			<th valign="top"></th>
			 			<th valign="top"><label>CUSTNAME</label></th>
			 			<th valign="top"><label>TOPORT</label></th>
			 			<th valign="top"><label>QUOTATION ID</label></th>
			 			<th valign="top"><label>SALES ID</label></th>
			 			<!-- <th valign="top"><label>SALESID FACTORY</label></th> -->
			 			<!-- <th valign="top"><label>FACTORY</label></th> -->
			 			<th valign="top"><label>ORDER OF COMPANY</label></th>
			 			<th valign="top"><label>CUSTOMER REF.</label></th>
			 			<th valign="top"><label>TOTAL CU.M</label></th>
			 			<th valign="top"><label>20' FCL</label></th>
			 			<th valign="top"><label>40' FCL</label></th>
			 			<th valign="top"><label>40' HQ</label></th>
			 			<th valign="top"><label>REMARK (SO)</label></th>
			 			<th valign="top"><label>REMARK (PI)</label></th>
			 			<th valign="top"><label>REQUEST SHIP DATE</label></th>
			 			<th valign="top"><label>SALE NAME</label></th>
			 			<th valign="top"><label>SO CONFIRM DATE</label></th>
			 			<th valign="top"><label>DATA ENTRY</label></th>
			 			<th valign="top"><label>REMARK REVISED</label></th>
			 			<th valign="top"><label>STATUS</label></th>

			 		</tr>

			 		<?php 
			 		foreach ($data as $key => $value) {  
			 			echo "<tr>";
			 			echo "<td align='center'><input type='checkbox' value='1' name=approve".$value['SALESID']."></td>";
			 			echo "<td align='center'><input type='checkbox' value='0' name=approve".$value['SALESID']."></td>";
			 			// echo "<td align='center'><input type='text' class='form-control' name=remarkapprove".$value['SALESID']."></input></td>";
			 			echo "<td><textarea style='display:none;' rows='2' cols='50' name=remarkapprove".$value['SALESID']."></textarea></td>";
			 			echo "<td>".$value['CUSTNAME']."</td>";
			 			echo "<td>".$value['TOPORT']."</td>";
			 			echo "<td>".$value['QUOTATIONID']."</td>";
			 			echo "<td>".$value['SALESID']."</td>";
			 			echo "<td align='center'>".$value['DSG_REFCOMPANYID']."</td>";
			 			echo "<td>".$value['CUSTOMERREF']."</td>";
			 			echo "<td>".$value['CUMX']."</td>";
			 			echo "<td>".number_format($value['CON20'])."</td>";
			 			echo "<td>".number_format($value['CON40'])."</td>";
			 			echo "<td>".number_format($value['CON40HQ'])."</td>";
			 			// echo "<td>".$value['REMARKS']."</td>";
			 			// echo "<td>".$value['DOCUINTRO']."</td>";
			 			echo "<td>".nl2br($value['REMARKS'])."</td>";
			 			echo "<td>".nl2br($value['DOCUINTRO'])."</td>";
			 			echo "<td>".$value['DSG_REQUESTSHIPDATE']."</td>";
			 			echo "<td>".$value['SALESNAME']."</td>";
			 			echo "<td>".date("d/m/Y", strtotime($value['SOCONDATE']))."</td>";
			 			echo "<td>".$value['CONNAME']."</td>";
			 			echo "<td>".$value['DSG_REASONREVISEORDERNAME']."</td>";
			 			if ($value['Revised']==2) {
			 				echo "<td color='#f7b733'>REVISED</td>";
			 			}else{
			 				echo "<td>WAITING</td>";
			 			}
			 			// echo "</tr>";

			 			// echo "<tr>";
			 			// echo "<td colspan='2'></td>";
			 			// echo "<td style='background-color: #cfd9df;'><label>REMARK (SO)</label></td>";
			 			// echo "<td colspan='16'>".nl2br($value['REMARKS'])."</td>";
			 			// echo "</tr>";

			 			// echo "<tr>";
			 			// echo "<td colspan='2'></td>";
			 			// echo "<td style='background-color: #cfd9df;'><label>REMARK (PI)</label></td>";
			 			// echo "<td colspan='16'>".nl2br($value['DOCUINTRO'])."</td>";
			 			echo "</tr>";
			 		} 
			 		?>

			 		<tr>
			 			<td colspan="21">
			 				<input type="hidden" name="nonce" value="<?php echo $nonce; ?>">
			 				<input type="hidden" name="soall">
			 				<input type="hidden" name="userid" value="<?php echo $userid; ?>">
			 				<button class="btn btn-success" id="btn_submit" ><i class="glyphicon glyphicon-envelope"></i> SUBMIT</button>
			 			</td>
			 		</tr>

				</table>
				</div>
			</form>
		</div>
	</div>

	<?php } ?>

</section>

<?php $this->push('scripts'); ?>
<script>
	jQuery(document).ready(function($) {
		
		$("input:checkbox").on('click', function() {
	    	var $box = $(this);
	    	if ($box.is(":checked")) {
	    		var group = "input:checkbox[name='" + $box.attr("name") + "']";
	    		$(group).prop("checked", false);
	        	$box.prop("checked", true);
	      	}else{
	        	$box.prop("checked", false);
	      	}
	        remarkbox($box.attr("name"));
	        // console.log($box.attr("name"));
	    });

		$('#btn_submit').on('click', function(event) {
			event.preventDefault();

			var sonull=[];
			var soall =[];
			var solist = <?php echo json_encode( $data ) ?>;
			for (i=0; i <= solist.length-1; i++) {
		        if ($("input[name=approve"+solist[i]['SALESID']+"]:checked").val() == undefined) {
		          sonull.push(solist[i]['SALESID']);
		        }else{
		        	soall.push(solist[i]['SALESID']);
		        }
		    }

		    $("input[name=soall]").val(soall.toString());
		    // if (sonull.length>0) {
		    // 	alert("Please checked Approve or Reject "+sonull.toString());
		    // }else{

		    	var _approve=[];
				var _reject=[];
				for (var i = 0; i < soall.length; i++) {
					if ($("input[name=approve"+soall[i]+"]:checked").val()==1) {
				    	_approve.push(soall[i]);
				    }
				    if ($("input[name=approve"+soall[i]+"]:checked").val()==0) {
				    	_reject.push(soall[i]);
				    }
				    if ($("input[name=approve"+soall[i]+"]:checked").val()==0 && $("textarea[name=remarkapprove"+soall[i]+"]").val()=="") {
				    	$("textarea[name=remarkapprove"+soall[i]+"]").focus();
				    	return false;
				    }
				}
				
				if (_approve.length>0 || _reject.length>0) {

					if (_approve.toString()!=="") {
						var str_confirm_a = "You are Approve Sale Order : "+_approve.toString()+" ?";
					}else{
						var str_confirm_a = "";
					}
					if (_reject.toString()!=="") {
						var str_confirm_r = "You are Reject Sale Order : "+_reject.toString()+" ?";
					}else{
						var str_confirm_r = "";
					}

			    	if (confirm(str_confirm_a+"\n"+str_confirm_r)) {
			    		
			    		$('#btn_submit').html('<i class="glyphicon glyphicon-envelope"></i> กำลังประมวลผล รอสักครู่...');
	      				$('#btn_submit').attr('disabled', true);

			    		$.ajax({
				          	url : '/kpi/waiting/approve',
				          	type : 'post',
				          	cache : false,
				          	dataType : 'json',
				          	data : $('form#form_approve').serialize(),
					    }).done(function(data) {
					        // console.log(data);
					        if (data.status==200) {
					        	alert(data.message);
					        	CloseWindowsInTime(2);
					        }else{
					        	alert(data.message);
					        }

					        $('#btn_submit').html('<i class="glyphicon glyphicon-envelope"></i> SUBMIT');
	      					$('#btn_submit').attr('disabled', false);

					    });
			    	}
			    }else{
			    	alert("Please checked Approve or Reject !");
			    }

		    	
		    // }
		    
			// console.log($('form#form_approve').serialize());
		});

		function remarkbox(name) {
  
		    if ($("input[name="+name+"]:checked").val()==1) {
		        $("textarea[name=remark"+name+"]").hide();
		    }else if($("input[name="+name+"]:checked").val()==0) {
		      	$("textarea[name=remark"+name+"]").show();
		    }else{
		      	$("textarea[name=remark"+name+"]").hide();
		    }
		      
		}

		function CloseWindowsInTime(t){
		    t = t*1000;
		    window.open('', '_self', '');
		    setTimeout("window.close()",t);
		}

	});
</script>
<?php $this->end(); ?>
