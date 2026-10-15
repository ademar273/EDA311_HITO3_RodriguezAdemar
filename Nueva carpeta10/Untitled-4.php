<?php
require 'db.php';
$t=tablaValida(); $cols=columnas($db,$t); $pk=clavePrimaria($cols);
$campos=array_column($cols,'Field');
$campo=in_array($_GET['orden']??'',$campos,true)?$_GET['orden']:$pk;
$asc=($_GET['dir']??'asc')!=='desc';

$orig=new ListaDoble(); $orig->cargarDesdeMySQL($db,$t);
$ord=new ListaDoble();  $ord->cargarDesdeMySQL($db,$t); $ord->ordenarBurbuja($campo,$asc);

function render($filas,$campos,$t,$pk,$acc){
  echo "<div class='scroll'><table><tr>";
  foreach($campos as $c) echo "<th>".h($c)."</th>";
  if($acc) echo "<th>Acciones</th>";
  echo "</tr>";
  foreach($filas as $f){
    echo "<tr>";
    foreach($campos as $c) echo "<td>".h($f[$c])."</td>";
    if($acc){
      $id=urlencode($f[$pk]);
      echo "<td><a href='form.php?t=$t&id=$id'>Editar</a>
        <form method='post' action='eliminar.php' onsubmit=\"return confirm('¿Eliminar registro?')\">
          <input type='hidden' name='t' value='$t'>
          <input type='hidden' name='id' value='".h($f[$pk])."'>
          <button class='del'>Eliminar</button>
        </form></td>";
    }
    echo "</tr>";
  }
  echo "</table></div>";
}

cabecera(ucfirst($t));
if(!empty($_GET['msg'])) echo "<p class='msg'>".h($_GET['msg'])."</p>";
echo "<p><a class='btn' href='form.php?t=$t'>+ Nuevo registro</a></p>";

echo "<h2>Registros (MySQL → lista doblemente enlazada, ".$orig->tamano()." nodos)</h2>";
render($orig->recorrer(),$campos,$t,$pk,true);

echo "<h2>Ordenamiento Bubble Sort sobre la lista</h2>
      <form method='get'><input type='hidden' name='t' value='$t'>Criterio: <select name='orden'>";
foreach($campos as $c) echo "<option ".($c===$campo?'selected':'').">".h($c)."</option>";
echo "</select> <select name='dir'><option value='asc'>Ascendente</option>
      <option value='desc' ".($asc?'':'selected').">Descendente</option></select>
      <button>Ordenar</button></form>";
render($ord->recorrer(),$campos,$t,$pk,false);
pie();