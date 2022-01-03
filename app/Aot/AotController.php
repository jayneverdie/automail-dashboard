<?php

namespace App\Aot;

use App\Common\View;
use App\Aot\AotAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AotController {

	public function __construct() {
		$this->view = new View;
		$this->aot = new AotAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function tbcVGM($request, $response, $args) {
		try {
			$email_dev = ['to' => ['jeamjit_p@deestone.com'], 'cc' => ['harit_j@deestone.com']];
            $email_internal_dev = ['to' => ['kittika_k@deestone.com'], 'cc' => []];
            $email_failed = ['to' => ['worawut_s@deestone.com'], 'cc' => []];

            $projectId = 35;
            $root = 'files/aot/tbc_vgm/';
            $rootTemp = 'temp/aot/tbc_vgm/';
            $getMail = $this->aot->getMailCustomer($projectId);
            $getMailFailed = $this->aot->getMailFailed($projectId);

            $files = $this->automail->getDirRoot($root);

            if (count($files)===0) {
				echo "The file does not exist";
				exit();
            }

            foreach ($files as $file) {
                if (gettype($file) !== 'array') {
                    if ($file !== 'Thumbs.db') {
                        preg_match('/CNT(a?.........)/i', $file, $output_no);
                        preg_match_all('/[^\d_.]+/i', $file, $output_type);
                        $allFiles[] = [
                            'file_name' => $file,
                            'cnt_no' => $output_no[0],
                            'file_type' => strtoupper($output_type[0][1]),
                            'file_size' => $this->aot->Size($root . $file)
                        ];
                    }
                }
            }
            sort($allFiles);
            // echo "<pre>".print_r($allFiles,true)."</pre>";
            // exit;
            $fileOutCombineFormat = [];
            $fileOutcorrectFormat = [];
            $fileIncorrectFormat = [];
            $tmp_file = [];
            $count_file = 0;
            $counter_tmp_file = 0;
            $cnt = '';
            $formatType = ['VGM','INV+PL','INSP'];

            foreach ($allFiles as $value) {

                if (!in_array($value['file_type'],$formatType)) {
                    $fileOutcorrectFormat[] = $value['file_name'];
                }else{
                    if (substr($value['file_name'], 0, 1) !== '_' && $this->aot->isAOTFileMatchAx($value['file_name']) === true) {

                            if ($value['cnt_no'] == $cnt) {
                                $tmp_file[$counter_tmp_file][] = $value['file_name'];
                            }else{
                                $count_file = 0;
                                $counter_tmp_file++;
                                $tmp_file[$counter_tmp_file][] = $value['file_name'];
                            }

                    } else {
                        // $fileIncorrectFormat[] = $value['file_name'];

                        if (count($this->aot->KeepLogFile($value['file_name'])) >= 3) {
                        // if ($this->aot->KeepLogFile($value['file_name']) === false) {
                            $fileIncorrectFormat[] = $value['file_name'];
                        }else{
                            $loggingKeep = $this->aot->loggingKeep(
                                $projectId,
                                'Keep File',
                                null,
                                null,
                                null,
                                null,
                                null,
                                $file,
                                'File'
                            );
                        }

                    }

                    $cnt = $value['cnt_no'];
                }

            }

            // echo "<pre>".print_r($tmp_file,true)."</pre>";
            // echo "<pre>".print_r($fileIncorrectFormat,true)."</pre>";
            // echo "<pre>".print_r($fileOutcorrectFormat,true)."</pre>";
            // echo "<pre>".print_r($getMail,true)."</pre>";
            // echo "<pre>".print_r($getMailFailed,true)."</pre>";
            // exit;

            for ($i=1; $i <= count($tmp_file); $i++) {

                if (count($tmp_file[$i]) === 0) {
                    exit('Folder is empty.' . PHP_EOL);
                }

                if (count($tmp_file[$i]) === 3) {

                    $subject = $this->aot->getTbcSubject($tmp_file[$i],'VGM');
                    $body = $this->aot->getBodyTbcVGM($tmp_file[$i]);
                    $files = $this->aot->pathTofile($tmp_file[$i],$root);

                    // echo $subject;
                    // echo "<br>";
                    // echo $body;
                    // exit;
                    // send External
                    $sendEmail = $this->email->sendEmail(
                        $subject,
                        $body,
                        $getMail['to'],
                        $getMail['cc'],
                        // $email_dev['to'],
                        // $email_dev['cc'],
                        [],
                        $files,
                        $getMail['sender'][0],
                        $getMail['sender'][0]
                    );

                    if($sendEmail == true) {
                        echo "Message has been sent External\n";

                        $sendEmailInternal = $this->email->sendEmail(
                            $subject,
                            $body,
                            $getMail['internal'],
                            // $email_internal_dev['to'],
                            [],
                            [],
                            $files,
                            $getMail['sender'][0],
                            $getMail['sender'][0]
                        );

                        $fileslogs = implode(" & ",$tmp_file[$i]);
                        // insert logs
                        $logging = $this->automail->logging(
                            $projectId,
                            'Message has been sent',
                            null,
                            null,
                            null,
                            null,
                            null,
                            $fileslogs,
                            'File'
                        );
                        $this->automail->loggingEmail($logging,$getMail['to'],1);
                        $this->automail->loggingEmail($logging,$getMail['cc'],2);
                        // $this->automail->loggingEmail($logging,$email_dev['to'],1);
                        // $this->automail->loggingEmail($logging,$email_dev['cc'],2);
                        if ($sendEmailInternal == true) {
                            $this->automail->loggingEmail($logging,$getMail['internal'],1);
                        }

                        foreach ($tmp_file[$i] as $file) {
                            // sendSucess movefile
                            $this->automail->initFolder($rootTemp, 'temp');
                            $this->automail->moveFile($root, $rootTemp, 'temp/', $file);
                        }

                    }

                }else{
                    foreach ($tmp_file[$i] as $k => $v) {
                        $fileOutCombineFormat[] = $v;
                    }
                }

            }
            // echo "<pre>".print_r($fileOutCombineFormat,true)."</pre>";
            // exit();
            if (count($fileIncorrectFormat) > 0) {

                $files_Incorrect = $this->aot->pathTofile($fileIncorrectFormat,$root);

                $sendEmailFailed = $this->email->sendEmail(
                    $this->aot->getTbcSubject($fileIncorrectFormat,'ERROR'),
                    $this->aot->getBodyTbcSIFailed($fileIncorrectFormat,'Fail sending due to no date of sending.'),
                    $getMailFailed['failed'],
                    // $email_failed['to'],
                    [],
                    [],
                    $files_Incorrect,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmailFailed == true) {
                    // sendfailed movefile
                    // foreach ($fileIncorrectFormat as $file) {
                    //     $this->automail->initFolder($root, 'failed');
                    //     $this->automail->moveFile($root, $root, 'failed/', $file);
                    // }
                }
            }

            if (count($fileOutcorrectFormat) > 0) {
                $files_Outcorrect = $this->aot->pathTofile($fileOutcorrectFormat,$root);

                $sendEmailFailedOut = $this->email->sendEmail(
                    $this->aot->getTbcSubject($fileOutcorrectFormat,'ERROR'),
                    $this->aot->getBodyTbcSIFailed($fileOutcorrectFormat,'Fail sending CNT not found'),
                    $getMailFailed['failed'],
                    // $email_failed['to'],
                    [],
                    [],
                    $files_Outcorrect,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmailFailedOut == true) {
                    // sendfailed movefile
                    foreach ($fileOutcorrectFormat as $file) {
                        $this->automail->initFolder($root, 'failed');
                        $this->automail->moveFile($root, $root, 'failed/', $file);
                    }
                }
            }

            if (count($fileOutCombineFormat) > 0) {
                $files_OutCombine = $this->aot->pathTofile($fileOutCombineFormat,$root);

                $sendEmailFailedOutCombine = $this->email->sendEmail(
                    $this->aot->getTbcSubject($fileOutCombineFormat,'ERROR'),
                    $this->aot->getBodyTbcSIFailed($fileOutCombineFormat,'Fail sending File not Combine'),
                    $getMailFailed['failed'],
                    // $email_failed['to'],
                    [],
                    [],
                    $files_OutCombine,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmailFailedOutCombine == true) {
                    // sendfailed movefile
                    foreach ($fileOutCombineFormat as $file) {
                        $this->automail->initFolder($root, 'failed');
                        $this->automail->moveFile($root, $root, 'failed/', $file);
                    }
                }
            }

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
    }

    public function tbcSI($request, $response, $args) {
        try {
            $email_dev = ['to' => ['jeamjit_p@deestone.com'], 'cc' => ['harit_j@deestone.com']];
			$email_internal_dev = ['to' => ['kittika_k@deestone.com'], 'cc' => []];
            $email_failed = ['to' => ['worawut_s@deestone.com'], 'cc' => []];

            $projectId = 36;
            $root = 'files/aot/tbc_si/';
            $rootTemp = 'temp/aot/tbc_si/';
            $getMail = $this->aot->getMailCustomer($projectId);
            $getMailFailed = $this->aot->getMailFailed($projectId);

            $files = $this->automail->getDirRoot($root);

            if (count($files)===0) {
				echo "The file does not exist";
				exit();
            }

            foreach ($files as $file) {
                if (gettype($file) !== 'array') {
                    if ($file !== 'Thumbs.db') {
                        preg_match('/CNT(a?.........)/i', $file, $output_no);
                        preg_match_all('/[^\d_.]+/i', $file, $output_type);
                        $allFiles[] = [
                            'file_name' => $file,
                            'cnt_no' => $output_no[0],
                            'file_type' => strtoupper($output_type[0][1]),
                            'file_size' => $this->aot->Size($root . $file)
                        ];
                    }
                }
            }

            sort($allFiles);
            // echo "<pre>".print_r($allFiles,true)."</pre>";
            // exit;
            $fileOutcorrectFormat = [];
            $fileIncorrectFormat = [];
            $tmp_file = [];
            $count_file_size = 0;
            $counter_tmp_file = 0;

            foreach ($allFiles as $value) {
                if($value['file_type'] != 'SI'){
                    $fileOutcorrectFormat[] = $value['file_name'];
                }else{

                    if (substr($value['file_name'], 0, 1) !== '_' && $this->aot->isAOTFileMatchSo($value['file_name']) === true) {

                        $typeOfFileSize = explode(' ', $value['file_size']);

                        if ($typeOfFileSize[1] === 'KB') {
                            $tmp_file_size = $typeOfFileSize[0] * 0.001;
                        } else {
                            $tmp_file_size = $typeOfFileSize[0];
                        }

                        $count_file_size += $tmp_file_size;

                        if (round($count_file_size, 2) <= 10.00) { // 10 MB
                            $tmp_file[$counter_tmp_file][] = $value['file_name'];
                        } else  {
                            $count_file_size = 0;
                            $counter_tmp_file++;
                            $tmp_file[$counter_tmp_file][] = $value['file_name'];
                        }
                    } else {
                        $fileIncorrectFormat[] = $value['file_name'];
                    }

                }
            }

            // echo "<pre>".print_r($tmp_file,true)."</pre>";
            // echo "<pre>".print_r($fileIncorrectFormat,true)."</pre>";
            // echo "<pre>".print_r($fileOutcorrectFormat,true)."</pre>";
            // echo "<pre>".print_r($getMail,true)."</pre>";
            // echo "<pre>".print_r($getMailFailed,true)."</pre>";
            // exit;

            for ($i=0; $i < count($tmp_file); $i++) {

                if (count($tmp_file[$i]) === 0) {
                    exit('Folder is empty.' . PHP_EOL);
                }

                $subject = $this->aot->getTbcSISubject($tmp_file[$i],'SI');
                $body = $this->aot->getBodyTbcSI($tmp_file[$i]);
                $files = $this->aot->pathTofile($tmp_file[$i],$root);

                    // echo $subject;
                    // echo "<br>";
                    // echo $body;
                    // exit;

                // send External
                $sendEmail = $this->email->sendEmail(
                    $subject,
                    $body,
                    $getMail['to'],
                    $getMail['cc'],
                    // $email_dev['to'],
                    // $email_dev['cc'],
                    [],
                    $files,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmail == true) {
                    echo "Message has been sent External\n";

                    $sendEmailInternal = $this->email->sendEmail(
                        $subject,
                        $body,
                        $getMail['internal'],
                        // $email_internal_dev['to'],
                        [],
                        [],
                        $files,
                        $getMail['sender'][0],
                        $getMail['sender'][0]
                    );

                    $fileslogs = implode(" & ",$tmp_file[$i]);
                    // insert logs
                    $logging = $this->automail->logging(
                        $projectId,
                        'Message has been sent',
                        null,
                        null,
                        null,
                        null,
                        null,
                        $fileslogs,
                        'File'
                    );

                    $this->automail->loggingEmail($logging,$getMail['to'],1);
                    $this->automail->loggingEmail($logging,$getMail['cc'],2);
                    // $this->automail->loggingEmail($logging,$email_dev['to'],1);
                    // $this->automail->loggingEmail($logging,$email_dev['cc'],2);
                    if ($sendEmailInternal == true) {
                        $this->automail->loggingEmail($logging,$getMail['internal'],1);
                    }
                    foreach ($tmp_file[$i] as $file) {
                        // sendSucess movefile
                        $this->automail->initFolder($rootTemp, 'temp');
                        $this->automail->moveFile($root, $rootTemp, 'temp/', $file);
                    }

                }
            }


            if (count($fileIncorrectFormat) > 0) {
                $files_Incorrect = $this->aot->pathTofile($fileIncorrectFormat,$root);

                $sendEmailFailed = $this->email->sendEmail(
                    $this->aot->getTbcSISubject($fileIncorrectFormat,'ERROR'),
                    $this->aot->getBodyTbcSIFailed($fileIncorrectFormat,'Fail sending due to no date of sending.'),
                    $getMailFailed['failed'],
                    // $email_failed['to'],
                    [],
                    [],
                    $files_Incorrect,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmailFailed == true) {
                    // sendfailed movefile
                    foreach ($fileIncorrectFormat as $file) {
                        $this->automail->initFolder($root, 'failed');
                        $this->automail->moveFile($root, $root, 'failed/', $file);
                    }
                }
            }

            if (count($fileOutcorrectFormat) > 0) {
                $files_Outcorrect = $this->aot->pathTofile($fileOutcorrectFormat,$root);

                $sendEmailFailedOut = $this->email->sendEmail(
                    $this->aot->getTbcSISubject($fileOutcorrectFormat,'ERROR'),
                    $this->aot->getBodyTbcSIFailed($fileOutcorrectFormat,'Fail sending CNT not found'),
                    $getMailFailed['failed'],
                    // $email_failed['to'],
                    [],
                    [],
                    $files_Outcorrect,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmailFailedOut == true) {
                    // sendfailed movefile
                    foreach ($fileOutcorrectFormat as $file) {
                        $this->automail->initFolder($root, 'failed');
                        $this->automail->moveFile($root, $root, 'failed/', $file);
                    }
                }
            }

        } catch (\Exception $e) {
			echo $e->getMessage();
		}
    }

    public function tbcDaily($request, $response, $args) {
        try {
            $projectId = 34;
            $root = 'D:\\automail\\aot_tbc\\';
            $rootTemp = 'D:\\automail\\aot_tbc\\';

            $files = $this->automail->getDirRoot($root);
            $getMail = $this->aot->getMailCustomer($projectId);

            if (count($files)===0) {
                $subject_failed = $this->aot->getTbcSubjectDaily();
                $body_failed = 'ไม่พบไฟล์รายงาน AOT VGM  INV PL daily report TBC (daily report) '.'<br>'.'โปรดตรวจสอบ';
                // echo "The file does not exist";
                $sendEmail = $this->email->sendEmail(
                    $subject_failed,
                    $body_failed,
                    $getMail['sender'],
                    [],
                    [],
                    [],
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );
				exit();
            }
            $targetFile = $root . \htmlspecialchars($files[0]);
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($targetFile);
			$worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // echo "<pre>".print_r($rows,true)."</pre>";
            if($rows[1][1]===NULL){
                $subject_failed = $this->aot->getTbcSubjectDaily();
                $body_failed = 'ไม่พบข้อมูลในรายงาน AOT VGM  INV PL daily report TBC (daily report) '.'<br>'.'โปรดตรวจสอบ';
                $sendEmail = $this->email->sendEmail(
                    $subject_failed,
                    $body_failed,
                    $getMail['sender'],
                    [],
                    [],
                    [$root.$files[0]],
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );
                exit();
            }

            // echo "<pre>".print_r($getMail,true)."</pre>";
            // exit;
            $sendEmail = $this->email->sendEmail(
                $this->aot->getTbcSubjectDaily(),
                $this->aot->getTbcBodyDaily(),
                $getMail['to'],
                [],
                [],
                [$root.$files[0]],
                $getMail['sender'][0],
                $getMail['sender'][0]
            );

            if($sendEmail === true) {
                echo "Message has been sent External\n";
                // insert logs
                $logging = $this->automail->logging(
                    $projectId,
                    'Message has been sent',
                    null,
                    null,
                    null,
                    null,
                    null,
                    $files[0],
                    'SSRS'
                );

                $sendEmailInternal = $this->email->sendEmail(
                    $this->aot->getTbcSubjectDaily(),
                    $this->aot->getTbcBodyDaily(),
                    $getMail['internal'],
                    [],
                    [],
                    [$root.$files[0]],
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                $this->automail->loggingEmail($logging,$getMail['to'],1);

                if($sendEmailInternal === true) {
                    echo "Message has been sent internal\n";
                    $this->automail->loggingEmail($logging,$getMail['internal'],2);
                }else{
                    echo $sendEmailInternal;
                }

                // sendSucess movefile
                $this->automail->initFolder($rootTemp, 'temp');
                $this->automail->moveFile($root, $rootTemp, 'temp/', $files[0]);

            }else{
                echo $sendEmail;
                // sendfailed movefile
                $this->automail->initFolder($root, 'failed');
                $this->automail->moveFile($root, $root, 'failed/', $files[0]);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function test($request, $response, $args)
    {
         try {
            // code
            $output = shell_exec("copy \\\\lungryn\automail\booking_confirmation\aot_daily_report\AOT_Booking_Daily_Report.xls files\\aot\\booking_daily_report\\AOT_Booking_Daily_Report.xls");

            echo "<pre>$output</pre>";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function shippingDocInsp($request, $response, $args) {
        try {
            $email_dev = ['to' => ['khanittha_p@deestone.com','witchuta_v@deestone.com'], 'cc' => ['wandee_h@deestone.com','harit_j@deestone.com']];
            $email_internal_dev = ['to' => ['chiraporn_o@deestone.com'], 'cc' => ['harit_j@deestone.com','wandee_h@deestone.com']];
            $email_failed = ['to' => ['harit_j@deestone.com'], 'cc' => []];

            $projectId = 41;
            $root = 'files/aot/shipping_doc_insp/';
            $rootTemp = 'temp/aot/shipping_doc_insp/';

            $getMail = $this->aot->getMailCustomer($projectId);
            $getMailFailed = $this->aot->getMailFailed($projectId);

            // echo "<pre>".print_r($getMail,true)."</pre>";
            // echo "<pre>".print_r($getMailFailed,true)."</pre>";
            // exit();

            $files = $this->automail->getDirRoot($root);

            if (count($files)===0) {
                echo "The file does not exist";
                exit();
            }

            // foreach ($files as $file) {
            //     if (gettype($file) !== 'array') {
            //         // echo substr($file, 12, 1);
            //         if ($file !== 'Thumbs.db' && substr($file, 12, 1) === '_') {
            //             preg_match('/CNT(a?.........)/i', $file, $output_no);
            //             preg_match_all('/[^\d_.]+/i', $file, $output_type);
            //             $allFiles[] = [
            //                 'file_name' => $file,
            //                 'cnt_no' => $output_no[0],
            //                 'file_type' => strtoupper(trim($output_type[0][1])),
            //                 'file_size' => $this->aot->Size($root . $file)
            //             ];
            //         }
            //     }
            // }
            $formatType = ['DOCS','INSP'];

            foreach ($files as $file) {
                if (gettype($file) !== 'array') {
                    
                        if ($file !== 'Thumbs.db' && substr($file, 12, 1) === '_') {
                            preg_match('/CNT(a?.........)/i', $file, $output_no);
                            preg_match_all('/[^\d_.]+/i', $file, $output_type);

                            if (in_array(strtoupper(trim($output_type[0][1])),$formatType)) {
                                $allFiles[] = [
                                    'file_name' => $file,
                                    'cnt_no' => $output_no[0],
                                    'file_type' => strtoupper(trim($output_type[0][1])),
                                    'file_size' => $this->aot->Size($root . $file)
                                ];
                            }
                            
                        }
                    
                    // echo substr($file, 12, 1);
                    
                }
            }
            // var_dump($files);
            // exit;
            sort($allFiles);
            // echo "<pre>".print_r($allFiles,true)."</pre>";
            // exit;
            $tmp_file = [];
            $counter_tmp_file = 0;
            $cnt = '';

            foreach ($allFiles as $key => $value) 
            {
                if ($value['cnt_no'] == $cnt) 
                {
                    $tmp_file[$counter_tmp_file][] = $value['file_name'];
                }
                else
                {
                    $count_file = 0;
                    $counter_tmp_file++;
                    $tmp_file[$counter_tmp_file][] = $value['file_name'];
                }
                $cnt = $value['cnt_no'];
            }

            $tmp_file_twin = [];
            $tmp_file_remove = [];
            for ($i=1; $i <= count($tmp_file); $i++) 
            { 
                if (count($tmp_file[$i])==2) {
                    foreach ($tmp_file[$i] as $key => $file) {
                        // $tmp_file_twin[] = $value;
                            preg_match('/CNT(a?.........)/i', $file, $output_no);
                            preg_match_all('/[^\d_.]+/i', $file, $output_type);
                            $tmp_file_twin[] = [
                                'file_name' => $file,
                                'cnt_no' => $output_no[0],
                                'file_type' => strtoupper(trim($output_type[0][1])),
                                'file_size' => $this->aot->Size($root . $file)
                            ];
                    }
                }
                else
                {
                    foreach ($tmp_file[$i] as $key => $file) {
                        // $tmp_file_twin[] = $value;
                        preg_match('/CNT(a?.........)/i', $file, $output_no);
                        preg_match_all('/[^\d_.]+/i', $file, $output_type);
                        $tmp_file_remove[] = [
                            'file_name' => $file,
                            'cnt_no' => $output_no[0],
                            'file_type' => strtoupper(trim($output_type[0][1])),
                            'file_size' => $this->aot->Size($root . $file)
                        ];
                    }
                }
            }
            // echo "<pre>".print_r($tmp_file_twin,true)."</pre>";
            // echo "<hr>";
            // echo "<pre>".print_r($tmp_file_remove,true)."</pre>";
            // exit;
            $fileOutCombineFormat = [];
            $fileOutcorrectFormat = [];
            $fileIncorrectFormat = [];
            $tmp_file = [];
            $count_file_size = 0;
            $counter_tmp_file = 0;
            $cnt = '';
            $formatType = ['DOCS','INSP'];

            $fileOutCombineFormat2 = [];
            $fileOutcorrectFormat2 = [];
            $fileIncorrectFormat2 = [];
            $tmp_file2 = [];
            $count_file_size2 = 0;
            $counter_tmp_file2 = 0;

            $check_key = [0,1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39,41,43,45,47,49];

            foreach ($tmp_file_twin as $key => $value) {
                if (!in_array($value['file_type'],$formatType)) {
                    $fileOutcorrectFormat[] = $value['file_name'];
                }else{

                    if (substr($value['file_name'], 12, 1) === '_' && $this->aot->isAOTFileMatchAx($value['file_name']) === true) {

                        $typeOfFileSize = explode(' ', $value['file_size']);

                        if ($typeOfFileSize[1] === 'KB') {
                            $tmp_file_size = $typeOfFileSize[0] * 0.001;
                        } else {
                            $tmp_file_size = $typeOfFileSize[0];
                        }

                        $count_file_size += $tmp_file_size;

                        if (round($count_file_size, 2) <= 10.00 || in_array($key,$check_key)) { // 10 MB
                            $tmp_file[$counter_tmp_file][] = $value['file_name'];
                        } else  {
                            $count_file_size = 0;
                            $counter_tmp_file++;
                            $tmp_file[$counter_tmp_file][] = $value['file_name'];
                        }
                    } else {
                        $fileIncorrectFormat[] = $value['file_name'];
                    }

                }
            }

            foreach ($tmp_file_remove as $error) {
                
                if (round($count_file_size2, 2) <= 10.00) { // 10 MB
                    $tmp_file2[$counter_tmp_file2][] = $error['file_name'];
                } else  {
                    $count_file_size2 = 0;
                    $counter_tmp_file2++;
                    $tmp_file2[$counter_tmp_file2][] = $error['file_name'];
                }

            }

            // echo "<pre>".print_r($tmp_file,true)."</pre>";
            // echo "<hr>";
            // echo "<pre>".print_r($tmp_file2,true)."</pre>";
            // echo "<hr>";
            // echo "<pre>".print_r($fileIncorrectFormat,true)."</pre>";
            // echo "<hr>";
            // echo "<pre>".print_r($fileOutcorrectFormat,true)."</pre>";
            // echo "<pre>".print_r($getMail,true)."</pre>";
            // echo "<pre>".print_r($getMailFailed,true)."</pre>";
            // exit();

            for ($i=0; $i < count($tmp_file); $i++) {
                if (count($tmp_file[$i]) === 0) {
                    exit('Folder is empty.' . PHP_EOL);
                }

                $subject = $this->aot->getSubjectShippingdocinsp($tmp_file[$i],"DOCS_INSP");
                $subjectAcc = $this->aot->getSubjectShippingdocinspAcc($tmp_file[$i]);
                $body = $this->aot->getBodyShippingdocinsp();
                $files = $this->aot->pathTofile($tmp_file[$i],$root);
                $filesAcc = $this->aot->pathTofileDocs($tmp_file[$i],$root);
                
                // echo $subject;
                // echo "<br>";
                // echo $body;
                // echo "<br>";
                // var_dump($files);
                // echo "<br>";
                // var_dump($filesAcc);
                // echo "<hr>";
                // exit;

                // send External
                $sendEmail = $this->email->sendEmail(
                    $subject,
                    $body,
                    $getMail['to'],
                    [],
                    // $email_dev['to'],
                    // $email_dev['cc'],
                    [],
                    $files,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmail == true) {
                    echo "Message has been sent External\n";

                    $sendEmailInternal = $this->email->sendEmail(
                        $subject,
                        $body,
                        $getMail['internal'],
                        // $email_internal_dev['to'],
                        [],
                        [],
                        $files,
                        $getMail['sender'][0],
                        $getMail['sender'][0]
                    );

                    $sendEmailInternalCC = $this->email->sendEmail(
                        $subjectAcc,
                        $body,
                        // $email_internal_dev['to'],
                        // $email_internal_dev['cc'],
                        $getMail['internalcc'],
                        [],
                        [],
                        $filesAcc,
                        $getMail['sender'][0],
                        $getMail['sender'][0]
                    );

                    $fileslogs = implode(" & ",$tmp_file[$i]);
                    // // insert logs
                    $logging = $this->automail->logging(
                        $projectId,
                        'Message has been sent',
                        null,
                        null,
                        null,
                        null,
                        null,
                        $fileslogs,
                        'File'
                    );
                    $this->automail->loggingEmail($logging,$getMail['to'],1);
                    $this->automail->loggingEmail($logging,$getMail['cc'],2);
                    // $this->automail->loggingEmail($logging,$email_dev['to'],1);
                    // $this->automail->loggingEmail($logging,$email_dev['cc'],2);
                    if ($sendEmailInternal == true) {
                        $this->automail->loggingEmail($logging,$getMail['internal'],1);
                        $this->automail->loggingEmail($logging,$getMail['internalcc'],2);
                        // $this->automail->loggingEmail($logging,$email_internal_dev['to'],1);
                    }

                    foreach ($tmp_file[$i] as $file) {
                        // sendSucess movefile
                        $this->automail->initFolder($rootTemp, 'temp');
                        $this->automail->moveFile($root, $rootTemp, 'temp/', $file);
                    }
                }

            }

            for ($i2=0; $i2 < count($tmp_file2); $i2++) {
                if (count($tmp_file2[$i2]) === 0) {
                    exit('Folder is empty.' . PHP_EOL);
                }

                $subject2 = "Automail NEW AOTC DOCS : ไม่สามารถส่งไฟล์ได้";
                // $subjectAcc = $this->aot->getSubjectShippingdocinspAcc($tmp_file[$i]);
                $body2 = "เนื่องจากวางไฟล์ไม่ครบ รายละเอียดตามไฟล์แนบ ";
                $files_error = $this->aot->pathTofile($tmp_file2[$i2],$root);

                // send failed
                $sendEmail2 = $this->email->sendEmail(
                    $subject2,
                    $body2,
                    $getMailFailed['failed'],
                    [],
                    // $email_dev['to'],
                    // $email_dev['cc'],
                    [],
                    $files_error,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmail2 == true) {
                    echo "Error วางไฟล์ไม่ครบ\n";

                    $fileslogs2 = implode(" & ",$tmp_file2[$i2]);
                    // // insert logs
                    $logging2 = $this->automail->logging(
                        $projectId,
                        'วางไฟล์ไม่ครบ',
                        null,
                        null,
                        null,
                        null,
                        null,
                        $fileslogs2,
                        'File'
                    );
                    $this->automail->loggingEmail($logging2,$getMailFailed['failed'],5);
                    
                    foreach ($tmp_file2[$i2] as $file2) {
                        // sendSucess movefile
                        $this->automail->initFolder($rootTemp, 'failed');
                        $this->automail->moveFile($root, $rootTemp, 'failed/', $file2);
                    }
                }

            }

            for ($i3=0; $i3 < count($fileIncorrectFormat); $i3++) {
                if (count($tmp_file2[$i2]) === 0) {
                    exit('Folder is empty.' . PHP_EOL);
                }

                $subject3 = "Automail NEW AOTC DOCS : ไม่สามารถส่งไฟล์ได้";
                // $subjectAcc = $this->aot->getSubjectShippingdocinspAcc($tmp_file[$i]);
                $body3 = "เนื่องจากรูปแบบไฟล์ไม่ถูกต้อง รายละเอียดตามไฟล์แนบ ";
                $files_error = $this->aot->pathTofile($fileIncorrectFormat[$i3],$root);

                // send failed
                $sendEmail3 = $this->email->sendEmail(
                    $subject3,
                    $body3,
                    $getMailFailed['failed'],
                    [],
                    // $email_dev['to'],
                    // $email_dev['cc'],
                    [],
                    $files_error,
                    $getMail['sender'][0],
                    $getMail['sender'][0]
                );

                if($sendEmail3 == true) {
                    echo "Error รูปแบบไฟล์ไม่ถูกต้อง\n";

                    $fileslogs3 = implode(" & ",$fileIncorrectFormat[$i3]);
                    // // insert logs
                    $logging3 = $this->automail->logging(
                        $projectId,
                        'รูปแบบไฟล์ไม่ถูกต้อง',
                        null,
                        null,
                        null,
                        null,
                        null,
                        $fileslogs3,
                        'File'
                    );
                    $this->automail->loggingEmail($logging3,$getMailFailed['failed'],5);
                    
                    foreach ($fileIncorrectFormat[$i3] as $file3) {
                        // sendSucess movefile
                        $this->automail->initFolder($rootTemp, 'failed');
                        $this->automail->moveFile($root, $rootTemp, 'failed/', $file3);
                    }
                }

            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
