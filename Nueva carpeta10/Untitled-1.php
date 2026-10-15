<?php
class Nodo {
  public array $dato; public ?Nodo $anterior=null; public ?Nodo $siguiente=null;
  public function __construct(array $dato){ $this->dato=$dato; }
}

class ListaDoble {
  private ?Nodo $cabeza=null; private ?Nodo $cola=null; private int $tamano=0;

  public function tamano():int { return $this->tamano; }

  public function insertarFinal(array $dato):void {
    $n=new Nodo($dato);
    if($this->cola===null){ $this->cabeza=$this->cola=$n; }
    else { $n->anterior=$this->cola; $this->cola->siguiente=$n; $this->cola=$n; }
    $this->tamano++;
  }

  public function recorrer():array {
    $r=[]; for($n=$this->cabeza;$n!==null;$n=$n->siguiente) $r[]=$n->dato; return $r;
  }

  public function recorrerInverso():array {
    $r=[]; for($n=$this->cola;$n!==null;$n=$n->anterior) $r[]=$n->dato; return $r;
  }

  public function cargarDesdeMySQL(mysqli $db,string $tabla):void {
    $res=$db->query("SELECT * FROM `$tabla`");
    while($fila=$res->fetch_assoc()) $this->insertarFinal($fila);
  }

  private function comparar($a,$b):int {
    if(is_numeric($a)&&is_numeric($b)) return $a<=>$b;
    return strcasecmp((string)$a,(string)$b);
  }

  public function ordenarBurbuja(string $campo,bool $asc=true):void {
    if($this->tamano<2) return;
    $fin=null;
    do {
      $huboCambio=false; $a=$this->cabeza;
      while($a->siguiente!==$fin){
        $b=$a->siguiente;
        $c=$this->comparar($a->dato[$campo]??'',$b->dato[$campo]??'');
        if(($asc?$c:-$c)>0){ [$a->dato,$b->dato]=[$b->dato,$a->dato]; $huboCambio=true; }
        $a=$b;
      }
      $fin=$a;
    } while($huboCambio);
  }
}