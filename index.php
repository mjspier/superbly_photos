<?php
/*!
 * superbly photos
 * http://www.superbly.ch
 * 
 * Copyright (C) 2013, Manuel Spierenburg (manuel@spierenburg.ch)
 * Licenced under GNU General Publi License, Version 3.0
 * http://www.gnu.org/licenses/gpl-3.0-standalone.html
 */ 

// config
$albumdir = './albums';

//error_reporting(0);
ini_set('memory_limit','512M');
$ignoreDirs = array('.','..','.cache');

function echoAlbums($dir){
	global $ignoreDirs;
	if(is_dir($dir)){
		if(in_array($dir,$ignoreDirs)){
			return;
		}
        if(false !== ($entries = scandir($dir))){
            foreach($entries as $entry){
                if(in_array($entry,$ignoreDirs))
					continue;
				$album = $dir.'/'.$entry;
				if(is_dir($album)){
                    echo "<ul>";
					echo "<li><a href=\"?dir=$album\">$entry</a></li>";
					echoAlbums($album);
                    echo "</ul>";
				}
			}
		}
	}
}

function echoImages($dir){
	global $ignoreDirs;
    if(false !== ($entries = scandir($dir))){
		echo "<ul class=\"media-grid\">";
        foreach($entries as $entry){
            if(in_array($entry,$ignoreDirs))
               continue; 
			$img = $dir.'/'.$entry;
			if(!is_dir($img)){
				echo "<li><a href=\"$img\"><img src=\"".resize($img,100,100,false)."\" alt=\"\"></a></li>";
			}

		}
		echo "</ul>";
	}
}


/**
 * Automatically resizes an image and returns formatted IMG tag
 *
 * @param string $path Path to the image file
 * @param integer $width Image of returned image
 * @param integer $height Height of returned image
 * @param boolean $aspect Maintain aspect ratio (default: true)
 */
 function resize($img, $width, $height, $aspect = true ) {

	$types = array('jpg','jpeg','png');
	$ext = pathinfo($img, PATHINFO_EXTENSION);
	$type = strtolower($ext);
	$cachedir = dirname($img).'/.cache/';
	
	if(!in_array($type,$types)){
		return $type;
	}		

	if(!file_exists($cachedir)){
		mkdir($cachedir,0777);
	}

        if (!($size = getimagesize($img)))
            return; // image doesn't exist

        if ($aspect) { // adjust to aspect. otherwise crop
            	if (($size[1]/$height) > ($size[0]/$width))  // $size[0]:width, [1]:height, [2]:type
                	$width = ceil(($size[0]/$size[1]) * $height);
            	else
                	$height = ceil($width / ($size[0]/$size[1]));
		$dir = "{$width}x{$height}";
        }else{
		if($size[0] < $size[1])
			$size[1] = $size[0];
		else
			$size[0] = $size[1];
		$dir = "{$width}x{$height}";
	}
	// filename	
	$cachefile = $cachedir.basename($img,'.'.$ext).'_'.$dir.'.'.$type;



        if (!file_exists($cachefile)) {
            $image = call_user_func('imagecreatefromjpeg', $img);
            if (function_exists("imagecreatetruecolor") && ($temp = imagecreatetruecolor ($width, $height))) {
                imagecopyresampled ($temp, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
             } else {
                $temp = imagecreate ($width, $height);
                imagecopyresized ($temp, $image, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
            }
            call_user_func("imagejpeg", $temp, $cachefile);
            imagedestroy ($image);
            imagedestroy ($temp);
        }
	return $cachefile;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Superbly Photos</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<style type="text/css">
			body{
				font-family: Georgia,"Times new Roman",Helvetica,sans-serif;
			}
			#container{
				width: 80%;
				margin: auto;
			}
			#header {
				background:#ddd;
				font-style: italic;
				padding: 5px;
				padding-left:10px;
				background-color: #949494;
				color: #FFFFFF;
				font-size: 18px;
                height: 25px;
            }
            #header a{
                padding: 5px;
                border: #474747;
                color: #282828;
                background-color: #bababa; 
                text-decoration: none;
                font-size: 12px;
            }

            #header a:hover{
                cursor: pointer;
                background-color: #585858;
                color: #e2e2e2;
            }
			#nav {
				float:left;
				margin: 10px;
                padding: 10px;
				width:200px;
				height: 100%;
				background-color: #bbcee4;
				border: solid 1px #7aa9e3;
				max-width: 27%;
			}
			#nav ul{
				list-style-type: none;
                border-left: solid 1px #7aa9e3;
                margin: 0;
                padding: 0;
                margin-left: 10px;
			}
            #nav ul li{
                padding-top: 10px;
            }
			#nav a{
                padding-left:10px;
				text-decoration:none;
				color: #447abc;
                border-bottom: solid 1px #7aa9e3;
			}
			#nav a:hover{
				color: #FFFFFF;	
			}
			#gallery {
				float: left;
				margin: 10px;
				max-width: 73%;
			}
			#gallery ul{
				list-style-type: none;
				margin: 0px;
				padding: 0px;
			}
			#gallery ul li{
				display: inline-block;
				margin: 0px;
				padding: 0px;
			}
			#gallery ul li a img{
				padding: 10px;
				margin: 10px;
				border: dashed 2px #D4D4D4;
			}
			#footer {
				clear: both;
            }
            .error{
                border: 1px solid #ff512f;
                color: #f80909;
                background-color: #e59a79;
                padding: 2px;
                padding-left: 5px;
            }
            .info{
                border: 1px solid #4e8b3e;
                color: #2b3927;
                background-color: #aeee9d;
                padding: 2px;
                padding-left: 5px;
            }
		</style>
	</head>
	<body>
	<div id="container">
		<div id="header">Superbly Photos
			<!-- add menu links here like <a href="index.php">home</a> -->
		</div>
		<div id="nav"><?php echoAlbums($albumdir); ?></div>
		<div id="gallery">
		<?php
		if(isset($_GET['dir'])){
			$imgDir = $_GET['dir'];
			echoImages($imgDir);
		}
		?>
		</div>
		<div id="footer"></div>
	</div>
	</body>
</html>
