<?php
 	require(  dirname( dirname( dirname( dirname( dirname(__FILE__) ) ) ) ) . '/wp-load.php' );

	$wp_uploads_dir = str_replace('\\', '/', wp_upload_dir() );
	$fileName = $_FILES["file"]["name"];
	$filePath = $wp_uploads_dir['basedir'] . '/WebApper/plupload/';
	if ( !file_exists($filePath) ) :
		mkdir($filePath, 0777, true);
	endif;

if (empty($_FILES) || $_FILES['file']['error']) {
  die('{"OK": 0, "info": "Failed to move uploaded file."}');
}
 
$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
 
$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
$filePath =  $wp_uploads_dir['basedir'] . '/WebApper/plupload/';

	if ( file_exists($filePath . $fileName) ) :
		$i = 1;
		while ( true ) :
			$temp_name = preg_replace('%\.([^\.]+?)$%', '_' . $i . '.$1', $fileName );
			if ( !file_exists( $filePath . $temp_name ) ) :
				$fileName = $temp_name;
				break;
			endif;
			$i++;
		endwhile;
	endif;
	$itemData = array(
		'attachment_type' => 'file',
		'attachment_file_path' => $filePath,
		'attachment_file_name' => $fileName,
		'attachment_created_on' => current_time('mysql'),
	);

$filePath =  $filePath . $fileName;

// Open temp file
$out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
if ($out) {
  // Read binary input stream and append it to temp file
  $in = @fopen($_FILES['file']['tmp_name'], "rb");
 
  if ($in) {
    while ($buff = fread($in, 4096))
      fwrite($out, $buff);
  } else
    die('{"OK": 0, "info": "Failed to open input stream."}');
 
  @fclose($in);
  @fclose($out);
 
  @unlink($_FILES['file']['tmp_name']);
} else
  die('{"OK": 0, "info": "Failed to open output stream."}');
 
 
// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
  // Strip the temp .part suffix off
  rename("{$filePath}.part", $filePath);
}

	
	$itemID = web_apper_insert_attachment( $itemData );
	
	// Send Response
	if ( $itemID ) :
		$response['success'] = true;
		$response['id'] = $itemID;
		$response['url'] = $wp_uploads_dir['baseurl'] . '/WebApper/plupload/' . $fileName;
		$response['data'] = $itemData;
	else :
		$response['success'] = false;
	endif;
	echo json_encode( $response );

?>