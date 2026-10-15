<?php
require_once __DIR__.'/src/ListaDoble.php';
if(!file_exists(__DIR__.'/config.php')) exit('Falta config.php (copia config.example.php).');
$cfg = require __DIR__.'/config.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$db = new mysqli($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['db'],$cfg['port']??3306);
$db->set_charset('utf8mb4');

const TABLAS = ['clientes','pedidos','productos'];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function tablaValida(){
  $t=$_REQUEST['t']??'';
  if(!in_array($t,TABLAS,true)){ http_response_code(400); exit('Tabla inválida'); }
  return $t;
}

function columnas($db,$t){ return $db->query("SHOW COLUMNS FROM `$t`")->fetch_all(MYSQLI_ASSOC); }

function clavePrimaria($cols){
  foreach($cols as $c) if($c['Key']==='PRI') return $c['Field'];
  return $cols[0]['Field'];
}

function foraneas($db,$cfg,$t){
  $s=$db->prepare("SELECT COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                   FROM information_schema.KEY_COLUMN_USAGE
                   WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND REFERENCED_TABLE_NAME IS NOT NULL");
  $s->bind_param('ss',$cfg['db'],$t); $s->execute(); $o=[];
  foreach($s->get_result() as $r) $o[$r['COLUMN_NAME']]=$r;
  return $o;
}

function cabecera($titulo){
  echo "<!doctype html><html lang='es'><head><meta charset='utf-8'><title>".h($titulo)."</title>
        <link rel='stylesheet' href='style.css'></head><body><nav><a href='index.php'>Inicio</a>";
  foreach(TABLAS as $x) echo "<a href='tabla.php?t=$x'>".ucfirst($x)."</a>";
  echo "</nav><main><h1>".h($titulo)."</h1>";
}
function pie(){ echo "</main></body></html>"; }