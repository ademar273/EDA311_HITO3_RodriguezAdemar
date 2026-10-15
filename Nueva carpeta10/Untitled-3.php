<?php
require 'db.php';
cabecera('Gestión de datos');
echo "<div class='cards'>";
foreach(TABLAS as $t) echo "<a class='card' href='tabla.php?t=$t'>".ucfirst($t)."</a>";
echo "</div>";
pie();