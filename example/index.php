<?php
require '../src/PreviewPrint.php';

use Ast\PreviewPrint\PreviewPrint;

PreviewPrint::optimizeOne('out/salida.jpg','demo2.jpeg','cover');
//PreviewPrint::mergeTwo('out/salida.jpg','demo2.jpeg','demo.jpg');

?>
<div style="padding: 20px; background: #0fc411;">
    <img src="out/salida.jpg" alt="demo">
</div>

