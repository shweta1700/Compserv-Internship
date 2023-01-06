<?php
   $host        = "host = 127.0.0.1";
   $port        = "port = 5432";
   $dbname      = "dbname = BHAIRAVNATHDB";
   $credentials = "user = postgres password=Shweta@1700";

   $conn = pg_connect( "$host $port $dbname $credentials"  );
   if(!$conn) {
      echo "Error : Unable to open database\n";
   } else {
      echo "Opened database successfully\n";
   }
?>