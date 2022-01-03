<?php
ob_start();
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=port.xlsx");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Port Export</title>
  </head>
  <style type="text/css">
    table {
      width: 100%;
      font-size: 10px;
    }
    tr td{
      padding: 10px;
    }
    th{
      background-color: yellow;
    }
  </style>
  <body>
    <table border="1" cellspacing="0" x:str BORDER="1">
      <thead>
        <th>Port</th>
        <th>Port2</th>
        <th>To</th>
        <th>Cc</th>
        <th>Internal</th>
        <th>Internal2</th>
      </thead>
      <tbody>
        
        <?php foreach ($rows as $key => $row) { ?>
           <tr>
            <td>
              <?php echo $row['port']; ?>
            </td>
            <td>
              <?php echo $row['port2']; ?>
            </td>
            <td>
              <?php echo $row['To']; ?>
            </td>
            <td>
              <?php echo $row['Cc']; ?>
            </td>
            <td>
              <?php echo $row['internal']; ?>
            </td>
            <td>
              <?php echo $row['internal2']; ?>
            </td>
           </tr> 

        <?php  } ?>

      </tbody>
    </table>
  </body>
</html>
