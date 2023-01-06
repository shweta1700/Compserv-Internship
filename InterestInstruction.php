<?php
 ob_start();
include "main.php";
require_once('Connection.php');


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
//error_reporting(0);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/InterestInstruct.jrxml';

$data = [];
//$row = [];
//$faker = Faker\Factandy::create('en_US');

$datefandmate = "'DD/MM/YYYY'";

$stdate_ = $_GET['stdate_'];
$bankName = $_GET['bankName'];
$etdate_ = $_GET['etdate_'];
$revoke = $_GET['revoke'];
$branch = $_GET['branch'];


// $bankName = str_replace(''', '', $bankName);
// $stdate_ = str_replace(''', '', $stdate);
// $etdate_ = str_replace(''', '', $etdate);
// $branchName = str_replace(''', '', $branchName);


$query='SELECT 
INTINSTRUCTION."INSTRUCTION_NO", INTINSTRUCTION."INSTRUCTION_DATE", INTINSTRUCTION."FROM_DATE", 
INTINSTRUCTION."DR_ACTYPE", INTINSTRUCTION."DR_AC_NO", INTINSTRUCTION."DR_PARTICULARS", 
INTINSTRUCTION."CR_ACTYPE", INTINSTRUCTION."CR_AC_NO", INTINSTRUCTION."CR_PARTICULARS", 
INTINSTRUCTION."SI_FREQUENCY", INTINSTRUCTION."LAST_EXEC_DATE", INTINSTRUCTION."UserCode",
VWALLMASTER."ac_name", VWALLMASTER_DR."ac_name","ownbranchmaster"."CODE","ownbranchmaster"."NAME"
FROM INTINSTRUCTION
LEFT OUTER JOIN VWALLMASTER ON VWALLMASTER.ac_no = CAST(INTINSTRUCTION."CR_AC_NO" AS character varying)
LEFT OUTER JOIN VWALLMASTER as VWALLMASTER_DR ON VWALLMASTER_DR.ac_no = CAST(INTINSTRUCTION."DR_AC_NO" AS character varying)
INNER JOIN  OWNBRANCHMASTER on INTINSTRUCTION."BRANCH_CODE" = OWNBRANCHMASTER."id"
WHERE (cast("INSTRUCTION_DATE" as date) between cast('. $stdate_ .' as date) 
AND cast('. $etdate_ .' as date)) AND intinstruction."BRANCH_CODE" = '.$branch.'';


// echo $query;
$sql =pg_query($conn,$query);
$i = 0;

// if (pg_num_rows($sql) == 0) {
//    include 'errandmsg.html';
// }else

    while ($row = pg_fetch_assoc($sql)) {
        $tmp = [
            'SIREC_DATE' => $row['SIREC_DATE'],
            'SI_NO' => $row['SI_NO'],
            'DR_ACTYPE' => $row['DR_ACTYPE'],
            'DR_AC_NO' => $row['DR_AC_NO'],
            'DR_PARTICULARS' => $row['DR_PARTICULARS'],
            'CR_ACTYPE' => $row['CR_ACTYPE'],
            'SCHEME' => $row['SCHEME'],
            'CR_AC_NO' => $row['CR_AC_NO'],
            'CR_PARTICULARS' => $row['CR_PARTICULARS'],
            'INSTRUCTION_DATE' => $row['INSTRUCTION_DATE'],
            'SI_FREQUENCY' => $row['SI_FREQUENCY'],
            'LAST_EXEC_DATE' => $row['LAST_EXEC_DATE'],
            'SYSADD_LOGIN' => $row['SYSADD_LOGIN'],
            'ac_name' => $row['ac_name'],
            'UserCode' => $row['UserCode'],
            'NAME' => $row['NAME'],
            'startDate' => $startDate,
            'endDate' => $endDate,
         


            'branch' => $branch,
            'stdate_' => $stdate_,
            'etdate_' => $etdate_,
            'revoke' => $revoke,
            'bankName' => $bankName,

        ];
        $data[$i] = $tmp;
        $i++;
        // echo '<pre>';
        // print_r($tmp);
        // echo '</pre>';
    
}
ob_end_clean();



$config = ['driver' => 'array', 'data' => $data];
//echo $query;
//print_r($data);
$repandt = new PHPJasperXML();
$repandt->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');
    
    
//}
?>
