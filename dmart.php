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

$filename = __DIR__.'/dmart.jrxml';

$data =[];
//$faker = Faker\Factory::create('en_US');

//$conn = pg_connect("host=127.0.0.1 dbname=inventrysystem user=postgres password=Shweta@1700");


//sql query
$query=' SELECT * from bill
inner join item_master on item_master.it_code=bill.it_code
inner join customer on customer.cid=bill.cid';

$sql =pg_query($conn,$query);


//iteration
$i = 0;
$amount=0;
$total = 0;

while($row= pg_fetch_assoc($sql)) {

	$amount = $row['quantity'] * $row['price'];
	$total = $total + $amount;

    $tmp =[
	'quantity'=> $row['quantity'],
	'cid'=>$row['cid'],
	'it_code'=>$row['it_code'],
	'date'=>$row['date'],
	'invoice'=>$row['invoice'],
	'name'=> $row['name'],
	'address'=>$row['address'],
	'mobile'=>$row['mobile'],
	'price'=>$row['price'],
	'description'=>$row['description'],

    'total'=>$total,
    'amount'=>$amount,
	 ];

    $data[$i] = $tmp;
    $i++;
}

ob_end_clean();
//print_r($data);


$config = ['driver'=>'array','data'=>$data];
//print_r($config);
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

?>