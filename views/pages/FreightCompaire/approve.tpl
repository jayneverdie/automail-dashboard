<?php $this->layout('layouts/clean', ['title' => 'Approve Freight Compaire']);?>
<?php 
    use App\FreightCompaire\FreightCompaireAPI;
    
    $PI_NO = $_GET["PI_NO"]; 
    $SO_NO = $_GET["SO_NO"]; 
    $Createby = $_GET["Createby"]; 
    $chooseuse = $_GET["chooseuse"];
    $nonce = $_GET["nonce"];
    $today = date('Y-m-d'); 

    $getnonce = (new FreightCompaireAPI)->checknonce($nonce);
?>
<style type="text/css">
    table {
        overflow-x: auto;
        white-space: nowrap;
        vertical-align: bottom;
        width: 90%;
            
    }

    input[type=checkbox] {
        width: 15px;
        height: 15px;
        vertical-align: baseline;
    }
</style>
<section class="content">

    <?php if($getnonce['status']==404){?>
        
        <div class="alert alert-danger" style="text-align: center;">
            <h1><?php echo $getnonce['message']; ?></h1> 
        </div>
    
    <?php } 
    else
    { 
        $getData = (new FreightCompaireAPI)->getLogData($SO_NO,$PI_NO,$Createby);

        $ApproveBy =(new FreightCompaireAPI)->getApproveBy();

        ?> 
        
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Approve Freight Compaire</h3> 
            </div>
    
            <div class="box-body">
                    <h3>Freight Prepaid</h3>
                    <div class="form-group">
                        <table width="80%"> 
                            <tr>
                                <td width="25%">PI no :  <?php echo $PI_NO; ?> </td>
                                <td width="25%">So no :  <?php echo $SO_NO; ?></td>
                                <td width="20%">Freight charge :  <?php echo $getData['FreightCharge']; ?> </td>
                                <td width="30%">Exchange rates :  <?php echo $getData['ExchangeRates']; ?> </td>
                            </tr>
                            <tr>
                                <td colspan="4">Customer name :  <?php echo $getData['CustName']; ?></td>
                            </tr>
                            <tr>
                                <td>Port :  <?php echo $getData['Port']; ?></td>
                                <td>Volume :  <?php echo $getData['Volume']; ?></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td>
                                    Compaire I :  
                                    <?php 
                                        if($getData['Compaire1'] == 0) { ?> 
                                            <input type='checkbox' name='check1' onclick='return false;' > 
                                        <?php } else { ?>
                                            <input type='checkbox' name='check1' onclick='return false;' checked>
                                        <?php }  
                                    ?>
                                </td>
                                <td>
                                    Compaire II :  
                                    <?php 
                                        if($getData['Compaire2'] == 0) { ?> 
                                            <input type='checkbox' name='check2' onclick='return false;' > 
                                        <?php } else { ?>
                                            <input type='checkbox' name='check2' onclick='return false;' checked>
                                        <?php }  
                                    ?>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td>Agent :  <?php echo $getData['Agent1']; ?></td>
                                <td>Agent :  <?php echo $getData['Agent2']; ?></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <table width ="100%">
                                        <tr>
                                            <td>
                                                <table>
                                                    <tr>
                                                        <td>Shipping Line :  </td>
                                                        <td> <?php echo $getData['ship1']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>Freight rate USD :   <?php echo $getData['rate1']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>Ens/Ams :   <?php echo $getData['Ens1']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>Lss :   <?php echo $getData['Lss1']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>War risk :   <?php echo $getData['War1']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total USD :  </td>
                                                        <td> <?php echo $getData['TotalUSD1'];?> / <?php echo $getData['Volume']; ?> </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table>
                                                    <tr>
                                                        <td>Shipping Line :  </td>
                                                        <td> <?php echo $getData['ship2']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>Freight rate USD :   <?php echo $getData['rate2']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>Ens/Ams :   <?php echo $getData['Ens2']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>Lss :   <?php echo $getData['Lss2']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>War risk :   <?php echo $getData['War2']; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total USD :  </td>
                                                        <td> <?php echo $getData['TotalUSD2']; ?> / <?php echo $getData['Volume']; ?> </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        <tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">Remark : <?php echo nl2br($getData['Remark1']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4">Choose to Use :  <?php echo $chooseuse; ?></td>
                            </tr>
                            <tr>
                                <td colspan="4">Approved by :  <?php echo $ApproveBy; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="form-group">
                        
                        <input type="radio" id="ok" name="approve_submit" value="Approve">
                        <label style="color: green;" for="ok"><i class="glyphicon glyphicon-ok-sign"></i> Approve</label> 
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" id="reject" name="approve_submit" value="Reject">
                        <label style="color: red;" for="reject"><i class="glyphicon glyphicon-remove-sign"></i> Reject</label>
                        &nbsp;&nbsp;&nbsp;
                        <textarea style="width: 500px;" name="remark_approve" id="remark_approve" disabled="disable" rows="1"></textarea>
                    </div>

    
                    <div class="form-group">
                        <button class="btn btn-primary" id="btn_submit" > SUBMIT </button>
                    </div>
                
            </div>
        </div>
        
    
</section>

<div class="modal" id="modal_confirm" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm <span id="modal_title"></span> ? </h4>
            </div>
            <div class="modal-body">
                <form id="form_confirm">

                    <input type="hidden" name="approveresult" id="approveresult">
                    <input type="hidden" name="approveremark" id="approveremark">

                    <input type="hidden" name="nonce" value="<?php echo $nonce; ?>">
                    <input type="hidden" name="pi" value="<?php echo $PI_NO; ?>">
                    <input type="hidden" name="so" value="<?php echo $SO_NO; ?>">
                   
                    <input type="hidden" name="FreightCharge" value="<?php echo $getData['FreightCharge']; ?>">
                    <input type="hidden" name="CustName" value="<?php echo $getData['CustName']; ?>">
                    <input type="hidden" name="Port" value="<?php echo $getData['Port']; ?>">
                    <input type="hidden" name="Compaire1" value="<?php echo $getData['Compaire1']; ?>">
                    <input type="hidden" name="Compaire2" value="<?php echo $getData['Compaire2']; ?>">
                    <input type="hidden" name="Agent1" value="<?php echo $getData['Agent1']; ?>">
                    <input type="hidden" name="Agent2" value="<?php echo $getData['Agent2']; ?>">
                    <input type="hidden" name="ship1" value="<?php echo $getData['ship1']; ?>">
                    <input type="hidden" name="ship2" value="<?php echo $getData['ship2']; ?>">
                    <input type="hidden" name="rate1" value="<?php echo $getData['rate1']; ?>">
                    <input type="hidden" name="rate2" value="<?php echo $getData['rate2']; ?>">
                    <input type="hidden" name="Ens1" value="<?php echo $getData['Ens1']; ?>">
                    <input type="hidden" name="Ens2" value="<?php echo $getData['Ens2']; ?>">
                    <input type="hidden" name="Lss1" value="<?php echo $getData['Lss1']; ?>">
                    <input type="hidden" name="Lss2" value="<?php echo $getData['Lss2']; ?>">
                    <input type="hidden" name="War1" value="<?php echo $getData['War1']; ?>">
                    <input type="hidden" name="War2" value="<?php echo $getData['War2']; ?>">
                    <input type="hidden" name="TotalUSD1" value="<?php echo $getData['TotalUSD1']; ?>">
                    <input type="hidden" name="TotalUSD2" value="<?php echo $getData['TotalUSD2']; ?>">
                    <input type="hidden" name="Remark1" value="<?php echo $getData['Remark1']; ?>">
                    <input type="hidden" name="Remark2" value="<?php echo $getData['Remark2']; ?>">
                    <input type="hidden" name="Createby" value="<?php echo $Createby; ?>">
                    <input type="hidden" name="ExchangeRates" value="<?php echo $getData['ExchangeRates']; ?>">
                    <input type="hidden" name="Volume" value="<?php echo $getData['Volume']; ?>">

                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-check" aria-hidden="true"></i> 
                        Submit
                    </button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                        <span class="glyphicon glyphicon-remove"></span>
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modal_alert" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="alert alert-danger" style="text-align: center;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="glyphicon glyphicon-remove"></span>
                  </button>
                <h4 class="modal-title"> <span id="alert_text"></span> </h4>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php $this->push('scripts'); ?>
<script>
    jQuery(document).ready(function($) 
    {
        var radio_val;
        var radio_text = "";
        var remark = "";
        $('#remark_approve').attr("disabled", true) ;

        $('input[type=radio][name=approve_submit]').change(function() 
        {
            if (this.value == 'Approve') {
                radio_val = 3;
                radio_text = "Approve";
                $('#remark_approve').attr("disabled", true) ;
                $('#remark_approve').val("");
            }
            else if (this.value == 'Reject') {
                radio_val = 0;
                radio_text = "Reject";
                $('#remark_approve').attr("disabled", false) ;
            }
        });

        $('#btn_submit').on('click', function(event) {
            if(radio_text == "")
            {
                $('#alert_text').text("Please choose approve or reject freight compaire ?");
                $('#modal_alert').modal({backdrop:'static'});
            }
            else
            {
                if(radio_text == "Reject" && $('#remark_approve').val() == "")
                {
                    $('#alert_text').text("Please input remark !!");
                    $('#modal_alert').modal({backdrop:'static'});
                }
                else
                {
                    $('#modal_confirm').modal({backdrop: 'static'});
                    $('#modal_title').text(radio_text);
                    $('#approveresult').val(radio_val);
                    $('#approveremark').val($('#remark_approve').val());
                }
            }
            
        }); 
        
        $('#form_confirm').submit(function(e) 
        {
            e.preventDefault();
            $('#modal_confirm').modal('hide');

            pi = $('#pi').val();
            nonce = $('#nonce').val();
            status = $('#approveresult').val();

            $.ajax({
                url : '/automail/freightcompaire/approve',
                type : 'post',
                cache : false,
                dataType : 'json',
                data : $('form#form_confirm').serialize(),
            }).done(function(data) {
                console.log(data);
                if (data.status==200) 
                {
                    window.location = '/automail/freightcompaire/approvecomplete';
                }
                else
                {
                    alert(data.message);
                }

            });

            
            
        });

    
    });
</script>
<?php $this->end(); ?>