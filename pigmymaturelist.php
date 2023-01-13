<?php
 ob_start();
include "main.php";
require_once('Connection.php');


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/pigmymaturedaclist.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";
$var = "'D'";
$AC_ACNOTYPE ="'PG'";
$AC_TYPE = "'9'";
$stdate = $_GET['stdate'];
$etdate = $_GET['etdate'];
$branch = $_GET['branch'];

// $var = $_GET['var'];



// $bankName = str_replace(''', '', $bankName);
// $stdate_ = str_replace(''', '', $stdate);
// $etdate_ = str_replace(''', '', $etdate);
// $branchName = str_replace(''', '', $branchName);


$query='SELECT  PGMASTER."AC_ACNOTYPE", PGMASTER."AC_TYPE", PGMASTER."AC_NO", PGMASTER."AC_NAME" 
, PGMASTER."AGENT_ACTYPE", PGMASTER."AGENT_ACNO", PGMASTER."AC_OPDATE", PGMASTER."AC_EXPDT"  
, SCHEMAST."S_NAME" AS SCHEME_NAME , DPMASTER."AC_NAME" AS agentname, VWTMPZBALANCEPIGMY.CLOSING_BALANCE
FROM PGMASTER

LEFT OUTER JOIN(SELECT PGMASTER."AC_ACNOTYPE", PGMASTER."AC_TYPE", PGMASTER."AC_NO", PGMASTER."AC_OPDATE", PGMASTER."AC_CLOSEDT" 
            , COALESCE(case PGMASTER."AC_OP_CD" WHEN '.$var.' THEN cast(PGMASTER."AC_OP_BAL" as FLOAT) else(-1) * cast(PGMASTER."AC_OP_BAL" as FLOAT)end,0) + COALESCE(CAST(TRAN_AMOUNT AS FLOAT),0) + COALESCE(DAILY_AMOUNT,0) CLOSING_BALANCE  
      ,COALESCE(case PGMASTER."AC_OP_CD" WHEN '.$var.' THEN CAST(PGMASTER."AC_PAYBLEINT_OP" AS FLOAT) else ((-1) * CAST(PGMASTER."AC_PAYBLEINT_OP" AS FLOAT))end,0) 
      + COALESCE(CAST(PIGMYTRAN."RECPAY_INT_AMOUNT" AS INTEGER),0) + COALESCE(CAST(DAILYTRAN."RECPAY_INT_AMOUNT" AS INTEGER),0)RECPAY_INT_AMOUNT 
FROM PGMASTER

LEFT OUTER JOIN (SELECT PIGMYTRAN."TRAN_ACNOTYPE", PIGMYTRAN."TRAN_ACTYPE", PIGMYTRAN."TRAN_ACNO",PIGMYTRAN."RECPAY_INT_AMOUNT", COALESCE(SUM(case PIGMYTRAN."TRAN_DRCR"
 WHEN '.$var.' THEN  CAST(PIGMYTRAN."TRAN_AMOUNT" AS FLOAT) ELSE((-1) * CAST(PIGMYTRAN."TRAN_AMOUNT" AS FLOAT) )END),0)TRAN_AMOUNT, 
      SUM(case PIGMYTRAN."TRAN_DRCR" WHEN '.$var.' THEN CAST(PIGMYTRAN."RECPAY_INT_AMOUNT" AS FLOAT) ELSE ((-1) * CAST(PIGMYTRAN."RECPAY_INT_AMOUNT"AS FLOAT))END)RECPAY_INT_AMOUNT
            FROM PIGMYTRAN 
            WHERE CAST("TRAN_DATE" AS DATE) <= cast('.$etdate.' as date)
            GROUP BY "TRAN_ACNOTYPE", "TRAN_ACTYPE", "TRAN_ACNO",PIGMYTRAN."RECPAY_INT_AMOUNT")PIGMYTRAN 
ON
PGMASTER."AC_NO" = CAST(PIGMYTRAN."TRAN_ACNO" AS FLOAT)

LEFT OUTER JOIN(SELECT DAILYTRAN."TRAN_ACNOTYPE", DAILYTRAN."TRAN_ACTYPE", DAILYTRAN."TRAN_ACNO",DAILYTRAN."RECPAY_INT_AMOUNT", COALESCE(SUM(case DAILYTRAN."TRAN_DRCR" WHEN '.$etdate.' THEN CAST(DAILYTRAN."TRAN_AMOUNT" AS FLOAT) ELSE((-1) * CAST(DAILYTRAN."TRAN_AMOUNT" AS FLOAT))END),0)DAILY_AMOUNT, 
      SUM(case DAILYTRAN."TRAN_DRCR" WHEN '.$var.' THEN CAST(DAILYTRAN."RECPAY_INT_AMOUNT" AS FLOAT) ELSE( (-1) * CAST( DAILYTRAN."RECPAY_INT_AMOUNT" AS FLOAT))END)RECPAY_INT_AMOUNT 
            From DAILYTRAN WHERE CAST(DAILYTRAN."TRAN_DATE" AS DATE) <= cast('.$etdate.' as date)
            AND CAST(DAILYTRAN."TRAN_STATUS" AS FLOAT) = 1
            GROUP BY DAILYTRAN."TRAN_ACNOTYPE", DAILYTRAN."TRAN_ACTYPE",DAILYTRAN."TRAN_ACNO",DAILYTRAN."RECPAY_INT_AMOUNT")DAILYTRAN
                 ON
CAST(PGMASTER."AC_NO" AS FLOAT) =  CAST(DAILYTRAN."TRAN_ACNO" AS FLOAT)
WHERE  
PGMASTER."AC_OPDATE" IS NULL 
OR CAST(PGMASTER."AC_OPDATE" AS DATE) <= CAST('.$etdate.' AS DATE)
AND PGMASTER."AC_CLOSEDT" IS NULL 
OR CAST(PGMASTER."AC_CLOSEDT" AS DATE)> CAST('.$etdate.' AS DATE))VWTMPZBALANCEPIGMY
ON PGMASTER."AC_NO" = VWTMPZBALANCEPIGMY."AC_NO"

left outer join DPMASTER on
CAST(PGMASTER."AGENT_ACNO" AS INTEGER)= DPMASTER."AC_NO"   
LEFT OUTER JOIN SCHEMAST ON  
PGMASTER."AC_ACNOTYPE" = SCHEMAST."S_ACNOTYPE"
WHERE
  VWTMPZBALANCEPIGMY.CLOSING_BALANCE<>0 
AND	PGMASTER."AC_ACNOTYPE" = '.$AC_ACNOTYPE.' AND PGMASTER."AC_TYPE" ='.$AC_TYPE.' 
AND PGMASTER."AC_EXPDT" >='.$stdate.' AND  PGMASTER."AC_EXPDT" <='.$etdate.'';


//echo $query;
$sql =pg_query($conn,$query);
$i = 0;

// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {
       

    $GRAND_TOTAL = $GRAND_TOTAL + $row['CLOSING_BALANCE'];
    if($type == ''){
        $type = $row['agentname'];
    }
    if($type == $row['agentname']){
        $schemeledger = $schemeledger + $row['CLOSING_BALANCE'];
    }else{
        $type = $row['agentname'];
        $schemeledger = 0;
        $schemeledger = $schemeledger + $row['CLOSING_BALANCE'];
    }

    $tmp = [
            'AC_ACNOTYPE' => $row['$AC_ACNOTYPE'],
            'AC_TYPE' => $row['$AC_TYPE'],
            'AC_NO' => $row['AC_NO'],
            'AC_NAME' => $row['AC_NAME'],
            'AGENT_ACTYPE' => $row['AGENT_ACTYPE'],
            'AGENT_ACNO' => $row['AGENT_ACNO'],
            'AC_OPDATE' => $row['AC_OPDATE'],
            'AC_EXPDT' => $row['AC_EXPDT'],
            'scheme_name' => $row['scheme_name'],
            'agentname' => $row['agentname'],
            'closing_balance' => $row['closing_balance'],
            


            'branch' => $branch,
            'stdate' => $stdate,
            'etdate' => $etdate,
            'scheme_code'=>$scheme_code,
            'var'=>$var,
            'count'=>$count,
            'field1' =>$field1,
            'field2' =>$GRAND_TOTAL,
            'GRAND_TOTAL' =>$GRAND_TOTAL,

            // 'revoke' => $revoke,
            // 'bankName' => $bankName,

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
    
    
// //}
?>
