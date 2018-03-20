<?php @include_once( $_SERVER["DOCUMENT_ROOT"]."/slimstat/inc.stats.php" ); ?>
<?
/*
Directory Listing Script - Version 2
====================================
Script Author: Ash Young <ash@evoluted.net>. www.evoluted.net
Layout: Manny <manny@tenka.co.uk>. www.tenka.co.uk

REQUIREMENTS
============
This script requires PHP and GD2 if you wish to use the 
thumbnail functionality.

INSTRUCTIONS
============
1) Unzip all files 
2) Edit this file, making sure everything is setup as required.
3) Upload to server
4) ??????
5) Profit!

CONFIGURATION
=============
Edit the variables in this section to make the script work as
you require.

Start Directory - To list the files contained within the current 
directory enter '.', otherwise enter the path to the directory 
you wish to list. The path must be relative to the current 
directory.
*/
$startdir = '.';

/*
Show Thumbnails? - Set to true if you wish to use the 
scripts auto-thumbnail generation capabilities.
This requires that GD2 is installed.
*/
$showthumbnails = true; 

/*
Show Directories - Do you want to make subdirectories available?
If not set this to false
*/
$showdirs = true;

/* 
Force downloads - Do you want to force people to download the files
rather than viewing them in their browser?
*/
$forcedownloads = false;

/*
Hide Files - If you wish to hide certain files or directories 
then enter their details here. The values entered are matched
against the file/directory names. If any part of the name 
matches what is entered below then it is now shown.
*/
$hide = array(
				'dlf',
				'index.php',
				'Thumbs',
				'.htaccess',
				'.htpasswd',
			);
			 
/* 
Show index files - if an index file is found in a directory
to you want to display that rather than the listing output 
from this script?
*/			
$displayindex = false;

/*
Allow uploads? - If enabled users will be able to upload 
files to any viewable directory. You should really only enable
this if the area this script is in is already password protected.
*/
$allowuploads = false;

/*
Overwrite files - If a user uploads a file with the same
name as an existing file do you want the existing file
to be overwritten?
*/
$overwrite = false;

/*
Index files - The follow array contains all the index files
that will be used if $displayindex (above) is set to true.
Feel free to add, delete or alter these
*/

$indexfiles = array (
				'index.html',
				'index.htm',
				'default.htm',
				'default.html'
			);
			
/*
File Icons - If you want to add your own special file icons use 
this section below. Each entry relates to the extension of the 
given file, in the form <extension> => <filename>. 
These files must be located within the dlf directory.
*/
$filetypes = array (
				'png' => 'jpg.gif',
				'jpeg' => 'jpg.gif',
				'bmp' => 'jpg.gif',
				'jpg' => 'jpg.gif', 
				'gif' => 'gif.gif',
				'zip' => 'archive.png',
				'rar' => 'archive.png',
				'exe' => 'exe.gif',
				'setup' => 'setup.gif',
				'txt' => 'text.png',
				'htm' => 'html.gif',
				'html' => 'html.gif',
				'fla' => 'fla.gif',
				'swf' => 'swf.gif',
				'xls' => 'xls.gif',
				'doc' => 'doc.gif',
				'sig' => 'sig.gif',
				'fh10' => 'fh10.gif',
				'pdf' => 'pdf.gif',
				'psd' => 'psd.gif',
				'rm' => 'real.gif',
				'mpg' => 'video.gif',
				'mpeg' => 'video.gif',
				'mov' => 'video2.gif',
				'avi' => 'video.gif',
				'eps' => 'eps.gif',
				'gz' => 'archive.png',
				'asc' => 'sig.gif',
			);
			
/*
That's it! You are now ready to upload this script to the server.

Only edit what is below this line if you are sure that you know what you
are doing!
*/
error_reporting(0);
if(!function_exists('imagecreatetruecolor')) $showthumbnails = false;
$leadon = $startdir;
if($leadon=='.') $leadon = '';
if((substr($leadon, -1, 1)!='/') && $leadon!='') $leadon = $leadon . '/';
$startdir = $leadon;

if($_GET['dir']) {
	//check this is okay.
	
	if(substr($_GET['dir'], -1, 1)!='/') {
		$_GET['dir'] = $_GET['dir'] . '/';
	}
	
	$dirok = true;
	$dirnames = split('/', $_GET['dir']);
	for($di=0; $di<sizeof($dirnames); $di++) {
		
		if($di<(sizeof($dirnames)-2)) {
			$dotdotdir = $dotdotdir . $dirnames[$di] . '/';
		}
		
		if($dirnames[$di] == '..') {
			$dirok = false;
		}
	}
	
	if(substr($_GET['dir'], 0, 1)=='/') {
		$dirok = false;
	}
	
	if($dirok) {
		 $leadon = $leadon . $_GET['dir'];
	}
}

if($_GET['download'] && $forcedownloads) {
	$file = str_replace('/', '', $_GET['download']);
	$file = str_replace('..', '', $file);

	if(file_exists($leadon . $file)) {
		header("Content-type: application/x-download");
		header("Content-Length: ".filesize($leadon . $file)); 
		header('Content-Disposition: attachment; filename="'.$file.'"');
		readfile($leadon . $file);
		die();
	}
}

if($allowuploads && $_FILES['file']) {
	$upload = true;
	if(!$overwrite) {
		if(file_exists($leadon.$_FILES['file']['name'])) {
			$upload = false;
		}
	}
	
	if($upload) {
		move_uploaded_file($_FILES['file']['tmp_name'], $leadon . $_FILES['file']['name']);
	}
}

$opendir = $leadon;
if(!$leadon) $opendir = '.';
if(!file_exists($opendir)) {
	$opendir = '.';
	$leadon = $startdir;
}

clearstatcache();
if ($handle = opendir($opendir)) {
	while (false !== ($file = readdir($handle))) { 
		//first see if this file is required in the listing
		if ($file == "." || $file == "..")  continue;
		$discard = false;
		for($hi=0;$hi<sizeof($hide);$hi++) {
			if(strpos($file, $hide[$hi])!==false) {
				$discard = true;
			}
		}
		
		if($discard) continue;
		if (@filetype($leadon.$file) == "dir") {
			if(!$showdirs) continue;
		
			$n++;
			if($_GET['sort']=="date") {
				$key = @filemtime($leadon.$file) . ".$n";
			}
			else {
				$key = $n;
			}
			$dirs[$key] = $file . "/";
		}
		else {
			$n++;
			if($_GET['sort']=="date") {
				$key = @filemtime($leadon.$file) . ".$n";
			}
			elseif($_GET['sort']=="size") {
				$key = @filesize($leadon.$file) . ".$n";
			}
			else {
				$key = $n;
			}
			$files[$key] = $file;
			
			if($displayindex) {
				if(in_array(strtolower($file), $indexfiles)) {
					header("Location: $file");
					die();
				}
			}
		}
	}
	closedir($handle); 
}

//sort our files
if($_GET['sort']=="date") {
	@ksort($dirs, SORT_NUMERIC);
	@ksort($files, SORT_NUMERIC);
}
elseif($_GET['sort']=="size") {
	@natcasesort($dirs); 
	@ksort($files, SORT_NUMERIC);
}
else {
	@natcasesort($dirs); 
	@natcasesort($files);
}

//order correctly
if($_GET['order']=="desc" && $_GET['sort']!="size") {$dirs = @array_reverse($dirs);}
if($_GET['order']=="desc") {$files = @array_reverse($files);}
$dirs = @array_values($dirs); $files = @array_values($files);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Directory Listing of <?=dirname($_SERVER['PHP_SELF']).'/'.$leadon;?></title>
<link rel="stylesheet" type="text/css" href="dlf/styles.css" />
<?
if($showthumbnails) {
?>
<script language="javascript" type="text/javascript">
<!--
function o(n, i) {
	document.images['thumb'+n].src = 'dlf/i.php?f='+i;

}

function f(n) {
	document.images['thumb'+n].src = 'dlf/trans.gif';
}
//-->
</script>
<?
}
?>
<style type="text/css">
<!--
body {
	background-color: #FFFFFF;
}
.style1 {color: #CCCCCC}
.style2 {color: #666666; }
-->
</style></head>
<body>
<div class="style2" id="container">
  <h1>Directory Listing of 
  <?=dirname($_SERVER['PHP_SELF']).'/'.$leadon;?></h1>
  <div id="breadcrumbs"> <a href="<?=$_SERVER['PHP_SELF'];?>">home</a> 
  <?
 	 $breadcrumbs = split('/', $leadon);
  	if(($bsize = sizeof($breadcrumbs))>0) {
  		$sofar = '';
  		for($bi=0;$bi<($bsize-1);$bi++) {
			$sofar = $sofar . $breadcrumbs[$bi] . '/';
			echo ' &gt; <a href="'.$_SERVER['PHP_SELF'].'?dir='.urlencode($sofar).'">'.$breadcrumbs[$bi].'</a>';
		}
  	}
  
	$baseurl = $_SERVER['PHP_SELF'] . '?dir='.$_GET['dir'] . '&amp;';
	$fileurl = 'sort=name&amp;order=asc';
	$sizeurl = 'sort=size&amp;order=asc';
	$dateurl = 'sort=date&amp;order=asc';
	
	switch ($_GET['sort']) {
		case 'name':
			if($_GET['order']=='asc') $fileurl = 'sort=name&amp;order=desc';
			break;
		case 'size':
			if($_GET['order']=='asc') $sizeurl = 'sort=size&amp;order=desc';
			break;
			
		case 'date':
			if($_GET['order']=='asc') $dateurl = 'sort=date&amp;order=desc';
			break;  
		default:
			$fileurl = 'sort=name&amp;order=desc';
			break;
	}
  ?>
  </div>
  <div id="listingcontainer">
    <div id="listingheader"> 
	<div id="headerfile"><a href="<?=$baseurl . $fileurl;?>">File</a></div>
	<div id="headersize"><a href="<?=$baseurl . $sizeurl;?>">Size</a></div>
	<div id="headermodified"><a href="<?=$baseurl . $dateurl;?>">Last Modified</a></div>
	</div>
    <div id="listing">
	<?
	$class = 'b';
	if($dirok) {
	?>
	<div><a href="<?=$_SERVER['PHP_SELF'].'?dir='.urlencode($dotdotdir);?>" class="<?=$class;?>"><img src="dlf/dirup.png" alt="Folder" /><strong>..</strong> <em>-</em> <?=date ("M d Y h:i:s A", filemtime($dotdotdir));?></a></div>
	<?
		if($class=='b') $class='w';
		else $class = 'b';
	}
	$arsize = sizeof($dirs);
	for($i=0;$i<$arsize;$i++) {
	?>
	<div><a href="<?=$_SERVER['PHP_SELF'].'?dir='.urlencode($leadon.$dirs[$i]);?>" class="<?=$class;?>"><img src="dlf/folder.png" alt="<?=$dirs[$i];?>" /><strong><?=$dirs[$i];?></strong> <em>-</em> <?=date ("M d Y h:i:s A", filemtime($leadon.$dirs[$i]));?></a></div>
	<?
		if($class=='b') $class='w';
		else $class = 'b';	
	}
	
	$arsize = sizeof($files);
	for($i=0;$i<$arsize;$i++) {
		$icon = 'unknown.png';
		$ext = strtolower(substr($files[$i], strrpos($files[$i], '.')+1));
		$supportedimages = array('gif', 'png', 'jpeg', 'jpg');
		$thumb = '';
		
		if($showthumbnails && in_array($ext, $supportedimages)) {
			$thumb = '<span><img src="dlf/trans.gif" alt="'.$files[$i].'" name="thumb'.$i.'" /></span>';
			$thumb2 = ' onmouseover="o('.$i.', \''.urlencode($leadon . $files[$i]).'\');" onmouseout="f('.$i.');"';
			
		}
		
		if($filetypes[$ext]) {
			$icon = $filetypes[$ext];
		}
		
		$filename = $files[$i];
		if(strlen($filename)>43) {
			$filename = substr($files[$i], 0, 40) . '...';
		}
		
		$fileurl = $leadon . $files[$i];
		if($forcedownloads) {
			$fileurl = $_SESSION['PHP_SELF'] . '?dir=' . urlencode($leadon) . '&download=' . urlencode($files[$i]);
		}

	?>
	<div><a href="<?=$fileurl;?>" class="<?=$class;?>"<?=$thumb2;?>><img src="dlf/<?=$icon;?>" alt="<?=$files[$i];?>" /><strong><?=$filename;?></strong> <em><?=round(filesize($leadon.$files[$i])/1024);?>KB</em> <?=date ("M d Y h:i:s A", filemtime($leadon.$files[$i]));?><?=$thumb;?></a></div>
	<?
		if($class=='b') $class='w';
		else $class = 'b';	
	}	
	?></div>
	<?
	if($allowuploads) {
		$phpallowuploads = (bool) ini_get('file_uploads');		
		$phpmaxsize = ini_get('upload_max_filesize');
		$phpmaxsize = trim($phpmaxsize);
		$last = strtolower($phpmaxsize{strlen($phpmaxsize)-1});
		switch($last) {
			case 'g':
				$phpmaxsize *= 1024;
			case 'm':
				$phpmaxsize *= 1024;
		}
	
	?>
	<div id="upload">
		<div id="uploadtitle"><strong>File Upload</strong> (Max Filesize: <?=$phpmaxsize;?>KB)</div>
		<div id="uploadcontent">
			<?
			if($phpallowuploads) {
			?>
			<form method="post" action="<?=$_SERVER['PHP_SELF'];?>?dir=<?=urlencode($leadon);?>" enctype="multipart/form-data">
			<input type="file" name="file" /> <input type="submit" value="Upload" />
			</form>
			<?
			}
			else {
			?>
			File uploads are disabled in your php.ini file. Please enable them.
			<?
			}
			?>
		</div>
		
	</div>
	<?
	}
	?>
  </div>
</div>
<div class="style1" id="copy"></div>
</body>
</html>
