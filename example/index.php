<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../src/PreviewPrint.php';
use Ast\PreviewPrint\PreviewPrint;

$postvalida = false;
$DS = DIRECTORY_SEPARATOR;
$dir_subida = __DIR__.$DS.'out'.$DS;
if(file_exists($dir_subida)){
    chmod($dir_subida,0777);
}else{
    mkdir($dir_subida,0777,true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    $postvalida = false;

    if( ($_FILES['image1']['name']!='') && ($_FILES['image2']['name']!='')  ){
        $fichero_subido1 = $dir_subida . basename($_FILES['image1']['name']);
        $fichero_subido2 = $dir_subida . basename($_FILES['image2']['name']);
        $postvalida = false;
        if (move_uploaded_file($_FILES['image1']['tmp_name'], $fichero_subido1) && move_uploaded_file($_FILES['image2']['tmp_name'], $fichero_subido2) ) {
            $postvalida = true;
            $filename = PreviewPrint::previewDual($fichero_subido1,$fichero_subido2,$dir_subida);
            $type = pathinfo($filename, PATHINFO_EXTENSION);
            $data = file_get_contents($filename);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            @unlink($fichero_subido1);
            @unlink($fichero_subido2);
        }
    }

}
?>
<html lang="es">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


    <title> Dual Image</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <h2 class="mb-50">Selecciona la imagen y aplica los filtros que desees</h2>
    <form method="POST" enctype="multipart/form-data" >
        <div class="row">
            <div class="col-6">
                <div class="input-group mb-3">
                    <div class="col-12">
                        <img id="output1"  alt="image1" class="img-fluid" style="display: none;"/>
                        <canvas id="canvas1" style="display:none"></canvas>
                    </div>
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupFileAddon01">Imagen 1</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="inputGroupFile01" name="image1" aria-describedby="inputGroupFileAddon01">
                        <label class="custom-file-label" for="inputGroupFile01">Choose file</label>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="input-group mb-3">
                    <div class="col-12">
                        <img id="output2"  alt="image2" class="img-fluid" style="display: none;"/>
                        <canvas id="canvas2" style="display:none"></canvas>
                    </div>
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupFileAddon02">Imagen 2</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="inputGroupFile02" name="image2" aria-describedby="inputGroupFileAddon02">
                        <label class="custom-file-label" for="inputGroupFile02">Choose file</label>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Generar</button>
            </div>
            <?php if($postvalida){?>
                <div class="col-12">
                    <img src="<?php echo $base64; ?>" alt="out" class="img-fluid">
                </div>
                <div class="col-12">
                    <a href="descargar.php" class="btn btn-success mt-10">Descargar</a>
                </div>
            <?php } ?>

        </div>

        <script>
            var input1, canvas1, context1, output1;
            input1 = document.getElementById("inputGroupFile01");
            canvas1 = document.getElementById("canvas1");
            context1 = canvas1.getContext('2d');
            output1 = document.getElementById("output1");
            input1.addEventListener("change", function() {
                var reader = new FileReader();
                reader.addEventListener("loadend", function(arg) {
                    var src_image = new Image();
                    src_image.onload = function() {
                        canvas1.height = src_image.height;
                        canvas1.width = src_image.width;
                        context1.drawImage(src_image, 0, 0);
                        var imageData = canvas1.toDataURL("image/png");
                        output1.src = imageData;
                        output1.style.display = 'block';
                    };
                    src_image.src = this.result;
                });
                reader.readAsDataURL(this.files[0]);
            });

            var input2, canvas2, context2, output2;
            input2 = document.getElementById("inputGroupFile02");
            canvas2 = document.getElementById("canvas2");
            context2 = canvas2.getContext('2d');
            output2 = document.getElementById("output2");
            input2.addEventListener("change", function() {
                var reader = new FileReader();
                reader.addEventListener("loadend", function(arg) {
                    var src_image = new Image();
                    src_image.onload = function() {
                        canvas2.height = src_image.height;
                        canvas2.width = src_image.width;
                        context2.drawImage(src_image, 0, 0);
                        var imageData = canvas2.toDataURL("image/png");
                        output2.src = imageData;
                        output2.style.display = 'block';
                    };
                    src_image.src = this.result;
                });
                reader.readAsDataURL(this.files[0]);
            });


        </script>

</div>
</body>
</html>