<?php

$dailydirpath = "/mnt/wd2tb/Daily";
$filecount = 0;
$dir = "/mnt/drobo/Movies";
#$dir = "/mnt/2TB/TV";
#$dir = "/mnt/2TB/Pictures";

$dirlist = array(
	"/mnt/drobo/Movies",
	"/mnt/drobo/Television",
#	"/mnt/drobo/Emulation",
	"/mnt/drobo/Cartoons"
	);

$path = '';

foreach($dirlist as $dir) {
#   $path = '';

   $stack[] = $dir;
   while ($stack) {
       $thisdir = array_pop($stack);
       if ($dircont = scandir($thisdir)) {
           $i=0;
           while (isset($dircont[$i])) {
               if ($dircont[$i] !== '.AppleDouble' && $dircont[$i] !== '.' && $dircont[$i] !== '..') {
                   $current_file = "{$thisdir}/{$dircont[$i]}";
                   if (is_file($current_file)) {
                        $is_png = eregi( "avi|mkv|mp4|mov|dv|flv",$current_file);
                        $is_badapple = eregi( "._",$current_file);
                        if ( $current_file != '.' && $current_file != '..' && $is_png){
				if ($is_badapple){
					}else{
                        $path[] = "{$thisdir}/{$dircont[$i]}";
			#print $dircont[$i];
			print "#";
			$filecount = $filecount+1;
			}
}
                   } elseif (is_dir($current_file)) {
                       #$path[] = "{$thisdir}/{$dircont[$i]}";
                       $stack[] = $current_file;
                   }
               }
               $i++;
           }
       }
   }
 #  return $path;
}

#print "$filecount\n";

//Ok, now for pain!


//set random number for the love of penguins
$random = rand(0, $filecount);

print "\nTotal Media: $filecount Today we pick: $path[$random]\n";


$videofile=$path[$random];
ob_start();
passthru("/usr/bin/ffmpeg -i \"{$videofile}\" 2>&1");
$duration = ob_get_contents();
ob_end_clean();

$search='/Duration: (.*?),/';
$duration=preg_match($search, $duration, $matches, PREG_OFFSET_CAPTURE, 3);
//TEST ECHO
echo $matches[1][0];


// and by magic its a new date!
$today = date("Y-m-d");

//SET THE NAME OF THE LINK
$link = "$today.mp4";

//REMOVE THE SYMBOLIC LINK WE CREATED BEFORE
unlink("$dailydirpath/{$link}");

//CREATE A SYMBOLIC LINK USING PHP
symlink("$path[$random]","$dailydirpath/{$link}");

//REMOVE THE SYMBOLIC LINK WE CREATED BEFORE
unlink("/mnt/wd2tb/Movies/Personal/Today.mp4");

//CREATE A SYMBOLIC LINK USING PHP
symlink("$dailydirpath/{$link}","/mnt/wd2tb/Movies/Personal/Today.mp4");


?>
