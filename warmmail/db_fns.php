<?php

function db_connect() {
   $result = new mysqli('localhost', 'root', '1111', 'mail');

   if (!$result) {
      return false;
   }
   return $result;
}

?>
