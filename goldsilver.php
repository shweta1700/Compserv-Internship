<?php
 ob_start();
include "main.php";
require_once('Connection.php');


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/goldsilverregister.jrxml';

$data = [];
$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";


$bankName = $_GET['bankName'];
$branch = $_GET['branch'];
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$AC_TYPE = $_GET['AC_TYPE'];
$AC_ACNOTYPE = $_GET['AC_ACNOTYPE'];
$FLAG1 = $_GET['FLAG1'];
$FLAG2 = $_GET['FLAG2'];
$TRAN_STATUS= $_GET['TRAN_STATUS'];




$query = '';
// $bankName = str_replace(''', '', $bankName);
// $stdate_ = str_replace(''', '', $stdate);
// $etdate_ = str_replace(''', '', $etdate);
// $branchName = str_replace(''', '', $branchName);


if($FLAG1 == 1) 
{$query .= 
  'SELECT GOLDSILVER."AC_ACNOTYPE" , GOLDSILVER."AC_TYPE", GOLDSILVER."AC_NO", LNMASTER."AC_NAME",
  LNMASTER."AC_SANCTION_AMOUNT", SCHEMAST."S_NAME", GOLDSILVER."RATE", GOLDSILVER."TRAN_STATUS" , GOLDSILVER.TRAN_DATE , 
  GOLDSILVER."ITEM_TYPE" , GOLDSILVER."TOTAL_WEIGHT_GMS", GOLDSILVER."CLEAR_WEIGHT_GMS",
  GOLDSILVER."TOTAL_VALUE", GOLDSILVER."BAG_RECEIPT_NO" 
  FROM
  (SELECT  "AC_ACNOTYPE" , "AC_TYPE", "AC_NO","SUBMISSION_DATE"  AS TRAN_DATE, "RATE", "TRAN_STATUS" , "ITEM_TYPE" , "TOTAL_WEIGHT_GMS" ,
   "CLEAR_WEIGHT_GMS","TOTAL_VALUE", "BAG_RECEIPT_NO"
            FROM GOLDSILVER WHERE "TRAN_STATUS" = 0
  UNION ALL  	  
  SELECT  "AC_ACNOTYPE" , "AC_TYPE", "AC_NO", "RETURN_DATE" AS TRAN_DATE,"RATE", "TRAN_STATUS" , "ITEM_TYPE" , "TOTAL_WEIGHT_GMS" ,
   "CLEAR_WEIGHT_GMS","TOTAL_VALUE", "BAG_RECEIPT_NO"
           FROM GOLDSILVER 
             WHERE "TRAN_STATUS" = 1 AND "RETURN_DATE" IS NOT NULL )
  GOLDSILVER
         LEFT OUTER JOIN LNMASTER ON
         CAST(GOLDSILVER."AC_NO" AS INTEGER) = LNMASTER."AC_NO"
         LEFT OUTER JOIN SCHEMAST ON
             GOLDSILVER."AC_TYPE" = SCHEMAST."S_APPL"
   WHERE
   GOLDSILVER."AC_ACNOTYPE" ='.$AC_ACNOTYPE.' 
   AND GOLDSILVER."AC_TYPE" ='.$AC_TYPE.'
   AND CAST(GOLDSILVER.TRAN_DATE AS DATE) >= CAST('. $stdate .' AS DATE) 
   AND CAST(GOLDSILVER.TRAN_DATE AS DATE)>= CAST('. $stdate .' AS DATE)
   AND GOLDSILVER."TRAN_STATUS" = '. $TRAN_STATUS .'';
}

else
{
$query.='SELECT GOLDSILVER."AC_ACNOTYPE" , GOLDSILVER."AC_TYPE", GOLDSILVER."AC_NO", LNMASTER."AC_NAME",
LNMASTER."AC_SANCTION_AMOUNT", SCHEMAST."S_NAME" , GOLDSILVER."RATE", GOLDSILVER."TRAN_STATUS",
GOLDSILVER."ITEM_TYPE" , GOLDSILVER."TOTAL_WEIGHT_GMS" , GOLDSILVER."CLEAR_WEIGHT_GMS",
GOLDSILVER."TOTAL_VALUE", GOLDSILVER."BAG_RECEIPT_NO",
( SELECT "SUBMISSION_DATE" AS TRAN_DATE FROM GOLDSILVER WHERE cast(GOLDSILVER."TRAN_STATUS" as integer) = 0 
 UNION ALL SELECT "RETURN_DATE" AS TRAN_DATE FROM GOLDSILVER WHERE cast(GOLDSILVER."TRAN_STATUS" as integer) =1 AND "RETURN_DATE" IS NOT NULL )
 FROM GOLDSILVER 
     LEFT OUTER JOIN LNMASTER ON
       CAST(GOLDSILVER."AC_NO" AS BIGINT) = CAST(LNMASTER."BANKACNO" AS BIGINT) 
     LEFT OUTER JOIN SCHEMAST ON
       GOLDSILVER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
     WHERE
         GOLDSILVER."AC_ACNOTYPE" ='.$AC_ACNOTYPE.'
        AND GOLDSILVER."AC_TYPE"= ' .$AC_TYPE.' 
       and CAST(GOLDSILVER."SUBMISSION_DATE" AS DATE)  >=  CAST('. $stdate .' AS DATE)  
   AND CAST(GOLDSILVER."RETURN_DATE" AS DATE)  <= CAST('. $etdate .'AS DATE) 
        AND GOLDSILVER."TRAN_STATUS"='. $TRAN_STATUS .'';
}

 //echo $query;
$sql =pg_query($conn,$query);
$i = 0;
$total_value;
$sub_total;
$grandtotal;

// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {
         $total_value = $row['TOTAL_WEIGHT_GMS'] * $row['RATE'];
         $sub_total = $total_value;
         $grandtotal = $total + $sub_total;


        $tmp = [
            'sr_no' => $row['sr_no'],
            'AC_ACNOTYPE' => $row['AC_ACNOTYPE'],
            'AC_TYPE' => $row['AC_TYPE'],
            'AC_NO' => $row['AC_NO'],
            'AC_NAME' => $row['AC_NAME'],
            'AC_SANCTION_AMOUNT' => $row['AC_SANCTION_AMOUNT'],
            'S_NAME' => $row['S_NAME'],
            'RATE' => $row['RATE'],
            'TRAN_STATUS' => $row['TRAN_STATUS'],
            'TOTAL_WEIGHT_GMS' => $row[ 'TOTAL_WEIGHT_GMS'],
            'CLEAR_WEIGHT_GMS' => $row['CLEAR_WEIGHT_GMS'],
            'TOTAL_VALUE' => $row['TOTAL_VALUE'],
            'BAG_RECEIPT_NO' => $row['BAG_RECEIPT_NO'],
            'tran_date' => $row['tran_date'],
            'ITEM_TYPE' => $row['ITEM_TYPE'],
            
         


            'branch' => $branch,
            'stdate' => $stdate,
            'etdate' => $etdate,
            'bankName' => $bankName,
            'FLAG1'=> $FLAG1,
            'FLAG2'=> $FLAG2,
            'sub_total' => $sub_total,
            'grandtotal' => $grandtotal,
         
           
        ];
        $data[$i] = $tmp;
        $i++;
        // echo '<pre>';
        // print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();


//echo $query;
$config = ['driver' => 'array', 'data' => $data];

//print_r($data);
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
//}
?>
