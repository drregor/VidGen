<?php

$dailydirpath = "/mnt/wd2tb/Daily";
$filecount = 0;
$dir = "/mnt/drobo/Movies";

$dirlist = array(
#	"/mnt/drobo/Movies",
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

print "\nTotal Media: $filecount\n";

//Ok, now for pain!
//so the idea here will be to do a while loop until we create a movie of this length
$timeline = 1800; //this measure will be minutes... actuall nm, seconds... cause we can!  (will work with some other project)
$maxtime = 10; //thisis a precentage 10% of the total time can be one video
$mintime = 1;
$totaltime = 0;
$fempegvar = "";
$count = 1;

//1800 is 30min... or should be
while ($totaltime < $timeline){

$vidpart = rand(($timeline*($mintime*.01)),($timeline*($maxtime*.01)));

//set random number for the love of penguins
$random = rand(0, $filecount);

// some sweet found code to get the length of the video...
$vidfile=$path[$random];
ob_start();
passthru("/usr/bin/ffmpeg -i \"{$vidfile}\" 2>&1");
$duration = ob_get_contents();
ob_end_clean();

$search='/Duration: (.*?),/';
$duration=preg_match($search, $duration, $matches, PREG_OFFSET_CAPTURE, 3);
// returns $matches[1][0] which we can now use

$vidtime = explode(":", $matches[1][0]);
$tvidtime = ($vidtime[0]*3600)+($vidtime[1]*60)+($vidtime[2]);
//Lets play god... in a time context... you know, irrelivant?
//aka, lets randomly select how much time we are going to use of this video

#print "$vidpart\n";

if ($tvidtime < $vidpart) {
	$vidpart = $tvidtime;
	$se = $tvidtime;
	$ss = 0;
}else{
	$ss = rand(0,$tvidtime-$vidpart);
	$se = $ss + $vidpart;
}

#print "Start: $ss End: $se Total Duration: $vidpart\n";
print "Using part of: $vidfile\n";
print "$vidtime[0] Hours | $vidtime[1] Min | $vidtime[2] Sec | Total Seconds: $tvidtime\n";
print "Start: $ss End: $se Total Duration: $vidpart\n";


//files are fun!  here we um do that thing... escape character bad things
$vidfile2 = str_replace(" ","\ ",$vidfile);
$vidfile = str_replace("(","\(",$vidfile2);
$vidfile2 = str_replace(")","\)",$vidfile);
$vidfile = str_replace("'","\'",$vidfile2);
$vidfile2 = str_replace("!","\!",$vidfile);

//well looks like I can't do what I want, so export each then combine
echo exec("ffmpeg -ss $ss -t $se -i $vidfile2 -ab 56 -ar 22050 -b 500 -s 320x240 ./temp/$count.mpg");

#$temp = $vidtime[1];
#$temp = $temp + 90;
#$temp = intval(strval($matches[1])); 
#echo intval($matches[1]);
#print "Show me the MIN! $temp\n"; 

$count = $count + 1;
$totaltime = $totaltime + $vidpart;
}

//Now to add the files together...mencoder!
//read in the dir... wait no I don't aka $count having it!  glory!
$robin = "";
$counta = 1;
while ($counta < $count) {
		$robin = "$robin ./temp/$counta.mpg";
		$counta = $counta + 1;
	}

print "$robin\n";
echo exec("mencoder -forceidx -oac copy -ovc mpg $robin -o end.mpg");

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
