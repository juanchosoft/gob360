<?php
session_start();
//Declaramos el directorio de las imagenes
$uploaddir = 'assets/pdf/';

$_SESSION['file']["nombrearchivo_pdf"] = "";

//Sino existe el directorio lo creamos
if (!is_dir($uploaddir)) {
    mkdir($uploaddir);
}

$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';

//Vamos a renombrar el fichero por uno aleatorio para que nunca se machaquen y se pierdan las imagenes
//$file = md5(basename($_FILES['userfile']['name'])) . strrchr(substr(str_shuffle($permitted_chars), 0, 10), '') . strrchr($_FILES['userfile']['name'], ".");
// Obtenemos la extensión del archivo de manera segura usando pathinfo
$extension = '.' . pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);

// Renombrar el fichero por uno aleatorio para que no se sobreescriban los archivos
$random_string = substr(str_shuffle($permitted_chars), 0, 10);
$file = md5(basename($_FILES['userfile']['name'])) . $random_string . $extension;

// Construimos la ruta de la imagen
$uploadfile = $uploaddir . $file;

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
    //Devolvemos la ruta completa para poder visualizarla.
    $_SESSION['file']["nombrearchivo_pdf"] = $file;
    echo $uploadfile; 
   // print_r($_SESSION['file']["nombrearchivo"]);
} else {
    echo "error upload file";
}