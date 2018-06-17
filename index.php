<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>The File Upload Form</title>
</head>

<body>

<form method="POST" id="fileupload" action='' enctype="multipart/form-data">
	<br/>Nazwa: <input type="file" name="file1" required>
	<br/>Wysokość:<input type="number" name="height" min = "1" max = "1000" required>
	<br/>Szerokość:<input type="number" name="width" min = "1" max = "1000" required>
	<br/><input type="submit" value="wyślij" name="form_submit">
</form>
<?php
require __DIR__ . '/vendor/autoload.php';

$file_allowed = ['jpg','jpeg'];
$dir = getcwd();
if (isset($_POST['form_submit'])) {
	// get form data
	$newheight = $_POST['height'];
	$newwidth = $_POST['width'];
	
	if (is_uploaded_file($_FILES['file1']['tmp_name'])) {
		// get uploaded file details
		$file = $_FILES['file1']['tmp_name'];
		$filename = $_FILES['file1']['name'];

		// check file extension
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if (!in_array($ext, $file_allowed)) {
			die("Nieodpowiedni rodzaj pliku. Dozwolone typy plików: " . implode(', ', $file_allowed));
		}
		// get image sizes
		list($width, $height) = getimagesize($file);
		// resize the image		
	    $src = imagecreatefromjpeg($file);
		$dst = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		
		// create directory if doesn't exist
		if (!file_exists($dir . '/uploads')) {
			mkdir($dir . '/uploads', 0777, true);
		}
		// save new image
		$new_filename = 'resized_' . $newwidth . 'x' . $newheight . '_' . $filename;
		$new_image = $dir . '/uploads/' . $new_filename;

		imagejpeg($dst, $new_image);

		// log details of operation
		$record = date('d/m/Y G:i:s') . " fileResizer created new file: " . $new_image . ", size: " . filesize($new_image) . 'b';
		$log = new Monolog\Logger('fileResizer');
		$log->pushHandler(new Monolog\Handler\StreamHandler('app.log', Monolog\Logger::INFO));
		$log->addInfo($record);
		
	} else {
		die('Nie udało się załadować pliku');
	}

	// show resized image
	echo "<img src = 'uploads/" . $new_filename . "'>";

}

?>

</body>
</html>


