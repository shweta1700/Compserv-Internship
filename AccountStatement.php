<?php
ob_start(); 
include "main.php";
require_once('dbconnect.php');

set_time_limit(100);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/AccountStatementNew.jrxml';

$data = [];
$faker = Faker\Factory::create('en_US');

// $conn = pg_connect("host=127.0.0.1 dbname=CBSLIVEDB user=postgres password=admin");

// variables
$bankName = $_GET['bankName'];
$sdate = $_GET['sdate'];
$stadate = $_GET['stadate'];
$edate = $_GET['edate'];
$branch = $_GET['branch'];
$scheme = $_GET['scheme'];
$fromacc = $_GET['fromacc'];
$toacc = $_GET['toacc'];
$print = $_GET['print'];
$printclose = $_GET['printclose'];

// echo $sdate;
// echo $edate;
// echo $stadate;

$stadate_ = str_replace("'", "", $stadate);
$edate_ = str_replace("'", "", $edate);
$branch = str_replace("'", "", $branch);
// $scheme = str_replace("'", "", $scheme);
// $fromacc = str_replace("'", "", $fromacc);
// $toacc = str_replace("'", "", $toacc);
$bankName = str_replace("'", "", $bankName);

$schm = "'CS'";
$schm1 = "'TR'";
$c = "'C'";
$d = "'D'";
$dateformat ="'DD/MM/YYYY'";
$o="'0'";

$query = ' SELECT  
           dpmaster."crtramt",dpmaster."crcsamt",dpmaster."drcsamt",dpmaster."drtramt",
           dpmaster."cramt",dpmaster."dramt",depotran."TRAN_TYPE",
           dpmaster."BANKACNO",dpmaster."AC_NAME",dpmaster."AC_NO",dpmaster."AC_TYPE",
           depotran."TRAN_DATE",depotran."CHEQUE_NO",depotran."TRAN_NO", depotran."NARRATION",
           dpmaster."BRANCH_CODE",customeraddress."AC_ADDR",dpmaster."AC_CUSTID",
           ownbranchmaster."NAME"
           FROM 
           (
               SELECT
               ledgerbalance(cast (schemast."S_APPL" as character varying), 
               dpmaster."BANKACNO",'.$sdate.',0,1)as ledgerbalance,
               coalesce(case when "TRAN_DRCR" ='.$c.' Then cast("TRAN_AMOUNT" as float) else 0 end, 0) as cramt,
               coalesce(case when "TRAN_DRCR" ='.$d.' Then cast("TRAN_AMOUNT" as float) else 0 end, 0) as dramt,
               coalesce(case when "TRAN_DRCR" ='.$c.' and "TRAN_TYPE" = '.$schm.' Then 
               cast("TRAN_AMOUNT" as float) else 0 end, 0) as crcsamt,
               coalesce(case when "TRAN_DRCR" ='.$d.' and "TRAN_TYPE" = '.$schm.' Then 
               cast("TRAN_AMOUNT" as float) else 0 end, 0) as drcsamt,
               coalesce(case when "TRAN_DRCR" ='.$c.' and "TRAN_TYPE" = '.$schm1.' Then 
               cast("TRAN_AMOUNT" as float) else 0 end, 0) as crtramt,
               coalesce(case when "TRAN_DRCR" ='.$d.' and "TRAN_TYPE" = '.$schm1.' Then 
               cast("TRAN_AMOUNT" as float) else 0 end, 0) as drtramt,
               dpmaster."BANKACNO",dpmaster."AC_NAME",dpmaster."AC_NO",dpmaster."AC_TYPE",depotran."TRAN_TYPE",
               depotran."TRAN_DATE",depotran."CHEQUE_NO",depotran."TRAN_NO", depotran."NARRATION",
               dpmaster."BRANCH_CODE",customeraddress."AC_ADDR",dpmaster."AC_CUSTID",ownbranchmaster."NAME"
               FROM dpmaster
               INNER JOIN depotran on dpmaster."BANKACNO" = depotran."TRAN_ACNO"
               INNER JOIN schemast on dpmaster."AC_TYPE" = schemast."id"
               INNER JOIN customeraddress ON dpmaster."AC_CUSTID" = customeraddress."id"
               INNER JOIN ownbranchmaster ON dpmaster."BRANCH_CODE" = ownbranchmaster."id"
           )dpmaster
           INNER JOIN depotran on dpmaster."BANKACNO" = depotran."TRAN_ACNO"
           INNER JOIN customeraddress ON dpmaster."AC_CUSTID" = customeraddress."id"
           INNER JOIN ownbranchmaster ON dpmaster."BRANCH_CODE" = ownbranchmaster."id"
           where cast(depotran."TRAN_DATE" as date)  >= '.$stadate.' ::date
           and cast(depotran."TRAN_DATE" as date)  <= '.$edate.' ::date
           and dpmaster."AC_NO" between '.$fromacc.' and '.$toacc.' 
           and dpmaster."AC_TYPE" = '.$scheme.' 
           and dpmaster."BRANCH_CODE" = '.$branch.' ';

        //    echo $query;
          
$sql =  pg_query($conn,$query);

$i = 0;

$GRAND_BALTOT = 0;
$GRAND_CASHTOT = 0;
$GRAND_TRANTOT = 0;
$GRAND_CASHTOT1 = 0;
$GRAND_TRANTOT1 = 0;
$CREDIT_TOTAL = 0;
$DEBIT_TOTAL = 0;
$BAL_TOTAL = 0;
$CREDIT_CSTR = 0;
$DEBIT_CSTR = 0;


if (pg_num_rows($sql) == 0) {
    include "errormsg.html";
}else {

while($row = pg_fetch_assoc($sql)){

    // print_r($sql);

    $GRAND_BALTOT = $row['cramt'] + $row['dramt'];
    $GRAND_CASHTOT = $GRAND_CASHTOT + $row['crtramt'];
    $GRAND_CASHTOT1 = $GRAND_CASHTOT1 + $row['drtramt'];
    $GRAND_TRANTOT = $GRAND_TRANTOT + $row['crcsamt'];
    $GRAND_TRANTOT1 = $GRAND_TRANTOT1 + $row['drcsamt'];
    $CREDIT_TOTAL = $CREDIT_TOTAL + $row['cramt'];
    $DEBIT_TOTAL = $DEBIT_TOTAL + $row['dramt'];
    $BAL_TOTAL = $BAL_TOTAL + $GRAND_BALTOT;
    $CREDIT_CSTR = $GRAND_CASHTOT + $GRAND_TRANTOT;
    $DEBIT_CSTR = $GRAND_CASHTOT1 + $GRAND_TRANTOT1;

    $tmp=[
        'TRAN_DATE' => $row['TRAN_DATE'],
        'CHEQUE_NO' => $row['CHEQUE_NO'],
        'TRAN_NO' => $row['TRAN_NO'],
        'NARRATION'=> $row['NARRATION'],
        'TRAN_TYPE'=> $row['TRAN_TYPE'],
        'AC_NAME'=> $row['AC_NAME'],
        'AC_NO' => $row['AC_NO'],
        'cramt'=> $row['cramt'],
        'dramt' => $row['dramt'],  
        'NAME' => $row['NAME'], 
        'ledgerbalance' => $row['ledgerbalance'],
        'balance' => $GRAND_BALTOT,
        'crtramt' => $GRAND_CASHTOT,
        'drtramt' => $GRAND_CASHTOT1,
        'crcsamt' => $GRAND_TRANTOT,
        'drcsamt' => $GRAND_TRANTOT1,
        'ctotal' => $CREDIT_TOTAL,
        'dtotal' => $DEBIT_TOTAL,
        'baltotal' => $BAL_TOTAL,
        'csumery' => $CREDIT_CSTR,
        'dsumery' => $DEBIT_CSTR,
    
        'bankName' => $bankName,
        'sdate' => $sdate,
        'startDate' => $startDate ,
        'endDate' => $endDate ,
        'stadate_' => $stadate_ ,
        'edate_' => $edate_ ,
        'branch' => $branch ,
        'scheme' => $scheme ,
        'fromacc' => $fromacc ,
        'toacc' => $toacc ,
        'custid' => $custid ,
        'custidwise' => $custidwise ,
        'rangewise' => $rangewise ,
        'print' => $print ,
        'printclose' => $printclose ,
    ];
    $data[$i]=$tmp;
    $i++;
    
}
ob_end_clean();

$config = ['driver'=>'array','data'=>$data];
// print_r($config);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
}   
?>

