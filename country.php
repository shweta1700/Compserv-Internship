<?php
ob_start();
error_reporting(0);
include "main.php";
require_once('connection.php');

// set_time_limit(100);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
use simitsdk\phpjasperxml\PHPJasperXML;

$filename = __DIR__.'/country.jrxml';

$data =[];
//$faker = Faker\Factory::create('en_US');

//$conn = pg_connect("host=127.0.0.1 dbname=inventrysystem user=postgres password=Shweta@1700");


//sql query
$query='SELECT * from input';

$sql =pg_query($conn,$query);

//$i = 0;
for($i=0;$row= pg_fetch_assoc($sql);) {
    $tmp =[
	'country'=> $row ['country'],
   ];
   
$data[$i] = $tmp;
echo "$row";
$i++;
    
}


ob_end_clean();



$config = ['driver'=>'array','data'=>$data];
//print_r($config);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

?>