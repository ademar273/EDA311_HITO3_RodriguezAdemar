<?php
require 'db.php';
$t=tablaValida(); $pk=clavePrimaria(columnas($db,$t));
if($_SERVER['REQUEST_METHOD']!=='POST'||!isset($_POST['id'])) exit('Solicitud inválida');
try{
  $s=$db->prepare("DELETE FROM `$t` WHERE `$pk`=?"); $s->bind_param('s',$_POST['id']); $s->execute();
  $m='Registro eliminado';
} catch(mysqli_sql_exception $e){
  $m='No se pudo eliminar (¿tiene registros relacionados?): '.$e->getMessage();
}
header("Location: tabla.php?t=$t&msg=".urlencode($m));