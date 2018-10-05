<?php
/**
 * Created by PhpStorm.
 * User: marcosamano
 * Date: 01/10/18
 * Time: 5:25 PM
 */
$file = __DIR__.DIRECTORY_SEPARATOR.'out'.DIRECTORY_SEPARATOR.'salida.jpg';

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($file));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
ob_clean();
flush();
readfile($file);
exit;