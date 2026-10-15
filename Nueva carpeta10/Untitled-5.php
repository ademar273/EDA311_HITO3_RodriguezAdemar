<?php
require 'db.php';
$t=tablaValida(); $cols=columnas($db,$t); $pk=clavePrimaria($cols); $fks=foraneas($db,$cfg,$t);
$id=$_REQUEST['id']??null; $errores=[]; $v=[];

if($id!==null){
  $s=$db->prepare("SELECT * FROM `$t` WHERE `$pk`=?"); $s->bind_param('s',$id); $s->execute();
  $v=$s->get_result()->fetch_assoc()?:[];
  if(!$v) exit('Registro no existe');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $datos=[];
  foreach($cols as $c){
    $n=$c['Field'];
    if(str_contains($c['Extra'],'auto_increment')||str_contains($c['Extra'],'GENERATED')) continue;
    if($id!==null && $n===$pk) continue;
    $x=trim($_POST[$n]??''); $v[$n]=$x;
    if($x===''){
      if($c['Null']==='NO' && $c['Default']===null){ $errores[]="«$n» es obligatorio."; continue; }
      $datos[$n]=null; continue;
    }
    if(preg_match('/int|decimal|float|double/',$c['Type']) && !is_numeric($x)){ $errores[]="«$n» debe ser numérico."; continue; }
    $datos[$n]=str_replace('T',' ',$x);
  }
  if(!$errores){
    $ks=array_keys($datos); $tipos=str_repeat('s',count($ks)); $vals=array_values($datos);
    if($id===null){
      $sql="INSERT INTO `$t` (`".implode('`,`',$ks)."`) VALUES (".implode(',',array_fill(0,count($ks),'?')).")";
    } else {
      $sql="UPDATE `$t` SET ".implode(',',array_map(fn($k)=>"`$k`=?",$ks))." WHERE `$pk`=?";
      $tipos.='s'; $vals[]=$id;
    }
    try{
      $s=$db->prepare($sql); $s->bind_param($tipos,...$vals); $s->execute();
      header("Location: tabla.php?t=$t&msg=".urlencode($id===null?'Registro creado':'Registro actualizado')); exit;
    } catch(mysqli_sql_exception $e){ $errores[]='Error de base de datos: '.$e->getMessage(); }
  }
}

cabecera(($id===null?'Registrar ':'Modificar ').$t);
foreach($errores as $e) echo "<p class='err'>".h($e)."</p>";
echo "<form method='post' class='f'><input type='hidden' name='t' value='$t'>";
if($id!==null) echo "<input type='hidden' name='id' value='".h($id)."'>";

foreach($cols as $c){
  $n=$c['Field'];
  if(str_contains($c['Extra'],'auto_increment')||str_contains($c['Extra'],'GENERATED')||($id!==null&&$n===$pk)) continue;
  $val=h(str_replace(' ','T',(string)($v[$n]??'')));
  $req=($c['Null']==='NO'&&$c['Default']===null)?'required':'';
  echo "<label>".h($n)." <small>(".h($c['Type']).")</small>";

  if(isset($fks[$n])){
    $r=$fks[$n];
    echo "<select name='$n' $req><option value=''>-- seleccione --</option>";
    $q=$db->query("SELECT * FROM `{$r['REFERENCED_TABLE_NAME']}` LIMIT 500");
    while($o=$q->fetch_assoc()){
      $k=$o[$r['REFERENCED_COLUMN_NAME']]; $et=implode(' - ',array_slice(array_values($o),0,2));
      echo "<option value='".h($k)."' ".((string)$k===(string)($v[$n]??'')?'selected':'').">".h($et)."</option>";
    }
    echo "</select>";
  } elseif(preg_match('/text/',$c['Type'])){
    echo "<textarea name='$n' $req>".h($v[$n]??'')."</textarea>";
  } else {
    $tp=preg_match('/int|decimal|float|double/',$c['Type'])?"number' step='any"
       :(str_starts_with($c['Type'],'datetime')||str_starts_with($c['Type'],'timestamp')?'datetime-local'
       :(str_starts_with($c['Type'],'date')?'date':'text'));
    echo "<input type='$tp' name='$n' value='$val' $req>";
  }
  echo "</label>";
}
echo "<button class='btn'>Guardar</button> <a href='tabla.php?t=$t'>Cancelar</a></form>";
pie();