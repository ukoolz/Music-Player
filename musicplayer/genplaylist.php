<?php 
 
 
 
 function printOutput($responseArray, $value){
         switch ($value) {
            case "1":
                  echo "<table style='width:80%' border='1'>";
                  for ($x = 0; $x <=50; $x++) {
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["stitle"]["value"];
                  echo "</td>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["album"]["value"];

                  echo "</td>";
                  echo "</tr>";


                  }
                  echo "</table>";
               break;
            case "2":
                  echo "The number of members in a band :";
                  echo $responseArray["results"]["bindings"][0]["callret-0"]["value"];
                  break;
            case "3":
                  echo "The number of bands which are called Nirvana :";
                  echo $responseArray["results"]["bindings"][0]["callret-0"]["value"];
                  break;
            case "4":
                  echo "The number of artists who are called John Williams:";
                  echo $responseArray["results"]["bindings"][0]["callret-0"]["value"];
                  break;
            case "5":
                  echo "The Liz Story is of artists type: ";
                  echo $responseArray["results"]["bindings"][0]["artisttype"]["value"];
                  break;
            case "6":
                  echo "Frank Sintara plays in following band: ";
                  echo "<table style='width:80%' border='1'>";
                  
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][0]["band"]["value"];
                  echo "</td>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][0]["name"]["value"];

                  echo "</td>";
                  echo "</tr>";


                  echo "</table>";
                  break;
            case "7":
                  echo "Was Keith Richards a member of The Rolling Stones? ";
                  $output= $responseArray["boolean"];
                  if($output==1){
                     echo " YES ";
                  }
                  else {
                     echo "NO";
                  }
                  break;
            case "8":
                  echo "Is there a group called The Notwist?";
                  $output= $responseArray["boolean"];
                  if($output==1){
                     echo " YES ";
                  }
                  else {
                     echo "NO";
                  }
                  break;
            
            case "9":
                  echo "List all the members of The Notwist";
                  echo "<table style='width:80%' border='1'>";
                  for ($x = 0; $x <=2; $x++) {
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["name"]["value"];

                  echo "</td>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["artist"]["value"];
                  
                  echo "</td>";
                  echo "</tr>";


                  }
                  echo "</table>";
               break;
            case "10":
                  echo "How many bands are called Queen?  ";

                  echo $responseArray["results"]["bindings"][0]["callret-0"]["value"];
                  break;
            case "11":
                  echo "The members of Muppets are:";
                  echo "<table style='width:80%' border='1'>";
                  for ($x = 0; $x <=8; $x++) {
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["artistname"]["value"];
                  echo "</td>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["artist"]["value"];

                  echo "</td>";
                  echo "</tr>";


                  }
                  echo "</table>";
                 // echo $responseArray["results"]["bindings"][0]["callret-0"]["value"];
                  break;
            case "12":
                  
                  echo "Is there a group called Michael Jackson? ";
                  $output= $responseArray["boolean"];
                  if($output==1){
                     echo " YES ";
                  }
                  else {
                     echo "NO";
                  }
                  break;
            case "13":
                  echo "Robbie Williams is a member of following bands: ";
                  echo "<br>";
                  echo "<table style='width:80%' border='1'>";
                  
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][0]["bandname"]["value"];
                  echo "</td>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][0]["band"]["value"];

                  echo "</td>";
                  echo "</tr>";


                  echo "</table>";
                  break;
				  
			case "14": echo "5 members of One Direction band";
					echo "<table style='width:80%' border='1'>";
                  for ($x = 0; $x < 5; $x++) {
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["name"]["value"];

                  echo "</td>";
                  echo "</tr>";
                  }
                  echo "</table>";
				break;
				
			case "15": echo "Top 20 tracks of The Rolling Stones based on track duration";
					echo "<table style='width:80%' border='1'>";
                  for ($x = 0; $x < 20; $x++) {
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["release"]["value"];
                  echo "</td>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["avg"]["value"];
                  echo "</td>";
                  echo "</tr>";
                  }
                  echo "</table>";
				break;
				
			case "16": echo "The URI of Switchfoot band is ";
				echo $responseArray["results"]["bindings"][0]["artist"]["value"];
				break;
				
			case "17": echo "Female members of the band ABBA";
				echo "<table style='width:80%' border='1'>";
                  for ($x = 0; $x < 1; $x++) {
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["name"]["value"];
                  echo "</td>";
                  echo "</tr>";
                  }
                  echo "</table>";
				break;
				
			case "18": echo "Top 10 tracks of The Beatles based on duration";
					echo "<table style='width:80%' border='1'>";
                  for ($x = 0; $x < 10; $x++) {
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["release"]["value"];
                  echo "</td>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["avg"]["value"];
                  echo "</td>";
                  echo "</tr>";
                  }
                  echo "</table>";
				break;
				
			case "19": echo "Name of bands of which Paul McCartney was a member of";
				echo "<table style='width:80%' border='1'>";
                  for ($x = 0; $x < 3; $x++) {
                  echo "<tr>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["band"]["value"];
                  echo "</td>";
                  echo "<td>";
                  echo $responseArray["results"]["bindings"][$x]["bandname"]["value"];
                  echo "</td>";
                  echo "</tr>";
                  }
                  echo "</table>";
				break;
         

         }
      }
  ?>
<?php




// takes two absolute paths and determines if one is a subdirectory of the other
// it doesn't care if it is an immediate child or 10 subdirectories deep...
// use absolute paths for both for best results
function is_child($parent, $child) {
  if(false !== ($parent = realpath($parent))) {
    $parent = fix_path($parent);
    if(false !== ($child = realpath($child))) {
      $child = fix_path($child);
      if(substr($child, 0, strlen($parent)) == $parent)
        return true;
    }
  }

  return false;
}

// fixes windows paths...
// (windows accepts forward slashes and backwards slashes, so why does PHP use backwards?
function fix_path($path) {
  return str_replace('\\','/',$path);
}

// Process the requested path into an absolute path
function reqToAbs($req) {
  global $Config;

  $absUnprocessed = $Config['MusicDir'] . "/$req";
  $abs = realpath($absUnprocessed);

  if ($abs === false || !file_exists($abs)) {
    // The requested path does not exist
    return false;
  }

  if (!is_child($Config['MusicDir'], $abs)) {
    // The requested path is outside of the allowed music dir
    return $Config['MusicDir'];
  }

  return $abs;
}

// Convert an absolute path into a request one
function absToReq($abs) {
  global $Config;

  if (is_child($Config['MusicDir'], $abs)) {
    // Chop off the musicdir part of the string at the beginning
    $req = substr($abs, strlen($Config['MusicDir']));
  } else {
    $req = '/';
  }

  return $req;
}

// Serve up a file using chunks
function readfile_chunked($filename) {
  global $Config;

  $buffer = '';
  $handle = fopen($filename, 'rb');
  if ($handle === false) {
    return false;
  }
  while (!feof($handle)) {
    $buffer = fread($handle, $Config['DownloadChunkSize']);
    print $buffer;
  }
  return fclose($handle);
}

function okayToDownload($absPath) {
  global $Config;

  return is_readable($absPath) && is_child($Config['MusicDir'], $absPath) && in_array(mime_content_type($absPath), $Config['Types']) && extensionOkay($absPath);
}

function extensionOkay($absPath) {
  global $Config;
  foreach ($Config['Extensions'] as $ext) {
	if (strripos($absPath, $ext) == strlen($absPath) - strlen($ext))
      return true;
  }
  return false;
}

function safeFilename($str) {
  $str = str_replace(' ', '_', $str);
  return preg_replace('/[^a-zA-Z0-9_.-]/', '', $str);
}

function connectToDB() {
  global $Config;
  return new mysqli($Config['DB']['Host'], $Config['DB']['User'], $Config['DB']['Password'], $Config['DB']['Database']);
}

// Returns whether or not the given song exists in the DB.
function songExistsInDB($absPath) {
  static $db = null;
  if ($db == null) {
    $db = connectToDB();
    fwrite(STDERR, "Connecting to DB.\n");
  }

  static $query = null;
  if ($query == null) {
    $query = $db->prepare('SELECT path FROM song WHERE path=?');
    fwrite(STDERR, "Preparing query.\n");
  }

  $query->bind_param('s', $absPath);
  $query->execute();
  $query->store_result();

  $numRows = $query->num_rows;

  $query->free_result();

  return ($numRows > 0);
}

// Returns the ID3 information of a song, using the database as a cache
function getSongInfo($absPath) {
  static $db = null;
  if ($db == null) $db = connectToDB();

  // If song already exists in DB, get it from there
  static $selectQuery = null;
  if ($selectQuery == null) $selectQuery = $db->prepare('SELECT path, title, album, artist, genre, bitrate, filesize, year, length FROM song WHERE path=?');
  $selectQuery->bind_param('s', $absPath);
  $selectQuery->execute();
  $selectQuery->store_result();
  if ($selectQuery->num_rows > 0) {
    $selectQuery->bind_result($path, $title, $album, $artist, $genre, $bitrate, $filesize, $year, $length);
    $selectQuery->fetch();
    $selectQuery->free_result();

    return array(
        'path' => $path,
        'title' => $title,
        'album' => $album,
        'artist' => $artist,
        'genre' => $genre,
        'bitrate' => $bitrate,
        'filesize' => $filesize,
        'year' => $year,
        'length' => $length,
    );
  } else {
    $selectQuery->free_result();
  }

  // If not, get the metadata from the file and store in in the DB
  require_once('/usr/share/php-getid3/getid3.php');

  $getID3 = new getID3;
  if (!is_file($absPath)) {
    return false;
  }

  $infoObj = $getID3->analyze($absPath);
  if (!$infoObj) {
    return false;
  }
  getid3_lib::CopyTagsToComments($infoObj);

  $info = array(
      'title' => commaJoin($infoObj['comments']['title']),
      'album' => commaJoin($infoObj['comments']['album']),
      'artist' => commaJoin($infoObj['comments']['artist']),
      'genre' => commaJoin($infoObj['comments']['genre']),
      'bitrate' => orNull($infoObj['bitrate']),
      'filesize' => orNull($infoObj['filesize']),
      'year' => mean($infoObj['comments']['year']),
      'length' => orNull($infoObj['playtime_seconds']),
  );

  // Store the metadata in the DB
  static $query = null;
  if ($query == null) $query = $db->prepare(
      'INSERT INTO song (path, title, album, artist, genre, bitrate, ' +
      'filesize, year, length) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
  $query->bind_param(
      'sssssiiid', $absPath, $info['title'], $info['album'], $info['artist'],
      $info['genre'], $info['bitrate'], $info['filesize'], $info['year'],
      $info['length']);
  $query->execute();

  print "(fresh) "; // TODO: remove if not debugging
  return $info;
}

function findKeyInArray($needle, $haystack) {
  $results = array();
  foreach ($haystack as $key => $val) {
    if ($key === $needle) {
      if (is_array($val)) {
        $results = array_merge($results, $val);
      } else {
        array_push($results, $val);
      }
    } else if (is_array($val)) {
      $result = findKeyInArray($needle, $val);
      $results = array_merge($results, $result);
    }
  }

  return $results;
}

function findLongestStringInArray($array) {
  $maxLength = 0;
  $longest = null;
  foreach ($array as $val) {
    $length = strlen($val);
    if ($length > $maxLength) {
      $longest = $val;
      $maxLength = $length;
    }
  }

  return $longest;
}

function mean($array) {
  if (count($array) == 0)
    return NULL;
  else
    return array_sum($array) / count($array);
}

function commaJoin($stringOrArray) {
  if (empty($stringOrArray))
	return NULL;

  if (is_array($stringOrArray))
	return join(', ', $stringOrArray);

  return $stringOrArray;
}

function orNull($val) {
  if (empty($val))
	return NULL;
  else
	return $val;
}

function pathurlencode($path) {
  return implode("/", array_map("rawurlencode", explode("/", $path)));
}

// Parses query strings in the standard way, without the [] for arrays
// and without replacing stuff with _. Based on
// http://www.php.net/manual/en/function.parse-str.php#76792
function parseQueryString($str) {
  # result array
  $arr = array();

  # split on outer delimiter
  $pairs = explode('&', $str);

  # loop through each pair
  foreach ($pairs as $i) {
    # split into name and value
    list($name,$value) = array_map('urldecode', explode('=', $i, 2));

    # if name already exists
    if( isset($arr[$name]) ) {
      # stick multiple values into an array
      if( is_array($arr[$name]) ) {
        $arr[$name][] = $value;
      }
      else {
        $arr[$name] = array($arr[$name], $value);
      }
    }
    # otherwise, simply stick it in a scalar
    else {
      $arr[$name] = $value;
    }
  }

  # return result array
  return $arr;
}

function songsHeader() {
?>
<div class="songs">
<?php
}

function listSong($path, $title, $album, $artist, $genre, $year, $length, $bitrate) {
  global $Config;
  if (empty($title))
    $title = "(unknown)";
  if (empty($album))
    $album = "(unknown)";
  if (empty($artist))
    $artist = "(unknown)";
?>
<div class="file">
  <a class="playlink" href="<?=pathurlencode($Config['ScriptRelDir'] . '/download' . absToReq($path))?>" title="<?=htmlentities(absToReq($path))?>">
    <span class="title"><?=htmlentities($title)?></span>
    <span class="album"><span class="tag">from </span><?=htmlentities($album)?></span>
    <span class="artist"><span class="tag">by </span><?=htmlentities($artist)?></span>
  </a>
</div>
<?php
}

function songsFooter() {
?>
</div>
<?php
}

abstract class FilesystemRegexFilter extends RecursiveRegexIterator {
  protected $regex;
  public function __construct(RecursiveIterator $it, $regex) {
    $this->regex = $regex;
    parent::__construct($it, $regex);
  }
}

class FilenameFilter extends FilesystemRegexFilter {
  public function accept() {
    return !$this->isFile() || preg_match($this->regex, $this->getFilename());
  }
}

?>
<?php

define("DB_SERVER", "localhost");
define("DB_USER", "root");
define("DB_PASS", "ukoolz");
define("DB_NAME", "toy");
	$servername = "localhost";
	$username = "root";
	$password = "ukoolz";
	$dbname = "mydb";
	$tracktable="MyTrackInformation";
	$tagtable="MyTrackTagCount";
	$similar="MyTrackSimilar";
	$artist="MyArtistInformation";
	$group="MyTrackGroup";
	
//Create a database connection
//Create a database connection

	$connect = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if(!$connect) {
		die("Failed to connect to the database" . mysql_error());
		}
	//select database
	
	$db_select = mysql_select_db(DB_NAME, $connect);
	
	if(!$db_select) {
		die("Failed to select database" . mysql_error());
		}

	
   /* $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS,DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }*/
    echo "Connected successfully";
if(isset($_POST['select']))
{//to run PHP script on submit
echo "me yha hu";
if(!empty($_POST['chktag']))
{
	$arr1=$_POST['chktag'];
	$query5="select distinct * from MyTrackInformation natural join
(select distinct(TrackArtistName) from MyTrackInformation T natural join MyTrackTagCount
where TagName like '%{$arr1[0]}%' AND TagCount IN
	(select MAX(TagCount) from
	(select * from MyTrackInformation natural join MyTrackTagCount) AS S
	 where  S.TrackArtistName = T.TrackArtistName
	GROUP BY TrackArtistName,TrackID,TrackTitle)) AS D;";
	echo $query5;
	$result1 = mysql_query($query5);
  echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
  while ($row1 = mysql_fetch_array($result1)) {
	 	
	 		echo "<tr>";
	 		echo "<td>Artist:{$row1{'TrackArtistName'}}</td>";
			echo "<td>Title:{$row1{'TrackTitle'}}</td>";
			echo "<td>Tid:{$row1{'TrackID'}}</td>";
			echo "</tr>\n";

		}
		echo "</tr>\n";
	
// Loop to store and display values of individual checked checkbox.

}
if(!empty($_POST['chkartist']))
{
	
	$arr1=$_POST['chkartist'];
	$no_artist = count($arr1);
	echo $no_artist;
	$query4="select distinct * from(select TrackTitle,TrackArtistName  from MyTrackInformation natural join 
((select T.PrimaryTrackID AS TrackID,T.Similarity from MyTrackSimilar T inner join
( select T1.TrackID PrimaryTrackID,T2.TrackID  from
	(select distinct (TrackID) from MyTrackInformation where TrackArtistName != \"{$arr1[0]}\") AS T1 
	inner join
	(select distinct(TrackID) from MyTrackInformation where TrackArtistName = \"{$arr1[0]}\") AS T2
) AS A
ON T.PrimaryTrackID = A.PrimaryTrackID AND T.TrackID = A.TrackID
order by T.Similarity desc )
UNION distinct
(select A1.TrackID,T5.Similarity from MyTrackSimilar T5 inner join
( select T6.TrackID PrimaryTrackID,T7.TrackID  from
	(select distinct (TrackID) from MyTrackInformation where TrackArtistName != \"{$arr1[0]}\") AS T6 
	inner join
	(select distinct(TrackID) from MyTrackInformation where TrackArtistName = \"{$arr1[0]}\") AS T7
) AS A1
ON T5.PrimaryTrackID = A1.TrackID AND T5.TrackID = A1.PrimaryTrackID
order by T5.Similarity desc )) AS D

order by Similarity desc)  AS D10 limit 100;";
echo $query4;
 $result1 = mysql_query($query4);
  echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
  while ($row1 = mysql_fetch_array($result1)) {
	 	
	 		echo "<tr>";
	 		echo "<td>Artist:{$row1{'TrackArtistName'}}</td>";
			echo "<td>Title:{$row1{'TrackTitle'}}</td>";
			echo "<td>Tid:{$row1{'TrackID'}}</td>";
			echo "</tr>\n";

		}
		echo "</tr>\n";
// Loop to store and display values of individual checked checkbox.

}

if(!empty($_POST['chktitle']))
{
	$stringarr=$_POST['chktitle'];
	$no_title = count($stringarr);
	echo $no_title;
	if($no_title==1)
	{
// Loop to store and display values of individual checked checkbox.
foreach($_POST['chktitle'] as $selected)
{
echo "Title:".$selected."</br>";
 }
				
				echo "\nye rha:\n".$stringarr[0];
				$qry1=null;
				$temporary="CREATE TEMPORARY TABLE initial5 (";
				$squery=null;
				$searchQ = "SELECT DISTINCT * FROM ";
				$searchQ.="{$tracktable} WHERE ";				
				
				foreach($stringarr as $i=>$d){
					/*OR each tag*/
					$searchQ.="TrackTitle LIKE \"%" . trim($d) . "%\" {$myopt} ";			
				}
				$searchQ = chop($searchQ, " {$myopt} ");
				$qry1=$temporary.$searchQ.");";
				$searchQ=$searchQ.";";
				$track="SELECT DISTINCT TrackID FROM {$tracktable} WHERE TrackTitle =";
				//$temp="SELECT TrackTitle, TrackArtistName, Year FROM {$tracktable} WHERE TrackID IN (SELECT TrackID FROM {$similar} WHERE TrackID IN (SELECT DISTINCT TrackID FROM {$tracktable} WHERE TrackTitle =";
			//	$myquery=$temp."\"{$stringarr[0]}\") ";
				$track=$track."\"{$stringarr[0]}\"; ";
				$myquery=$myquery."ORDER BY Similarity DESC) LIMIT 100;";
				//echo $track;
				$result = mysql_query($track);
				$tid="";
				
	 	while ($row = mysql_fetch_array($result)) {
	 		$tid=$row{'TrackID'};
	 //	echo $tid;

		}
	//	echo $tid;
	$query1="select distinct * from (
(select t1.TrackArtistName,t1.TrackTitle,t2.Similarity
from MyTrackInformation t1 inner join 
(select TrackID,Similarity from MyTrackSimilar where PrimaryTrackID = \"{$tid}\" 
	order by Similarity desc ) t2
on t1.TrackID = t2.TrackID)
union distinct
 (select t6.TrackArtistName,t6.TrackTitle,t7.Similarity from MyTrackInformation t6 inner join 
  ((select t3.PrimaryTrackID,t3.Similarity from MyTrackSimilar t3 inner join 
   (select distinct(TrackID),Similarity from MyTrackSimilar 
   where PrimaryTrackID = \"{$tid}\"  order by Similarity desc limit 0,100) t4 on t3.PrimaryTrackID = t4.TrackID 
	order by t3.Similarity desc ) 
  ) t7 on t6.TrackID = t7.PrimaryTrackID) 
  order by Similarity desc  ) AS tbl  limit 0,100;";
 //echo $query1;
  $result1 = mysql_query($query1);
  echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
  while ($row1 = mysql_fetch_array($result1)) {
	 	
	 		echo "<tr>";
	 		echo "<td>Artist:{$row1{'TrackArtistName'}}</td>";
			echo "<td>Title:{$row1{'TrackTitle'}}</td>";
			echo "</tr>\n";

		}
		echo "</tr>\n";
}

else {
$track="SELECT DISTINCT TrackID FROM {$tracktable} WHERE TrackTitle IN (\"abcd\"";
$query2=null;
		// ('TRAAABD128F429CF47','TRAAAED128E0783FAB')
		 foreach($stringarr as $i){
					$track=$track.",\"{$i}\"";
				}
				$track=$track.");";
				
				echo $track;
				$tid1="(";
				$result2 = mysql_query($track);
				while ($row1 = mysql_fetch_array($result2)) {
					$tid1=$tid1."\"";
	 			$tid1=$tid1.$row1{'TrackID'}."\",";
	 			
		}
		$tid1=$tid1."\"TRAAAED128E0783FAB\")";
		//echo $tid1;
		$query2="select TrackArtistName,TrackTitle from (
		(select distinct * from 
		(select  @rowNum := @rowNum +1 as ROWNUM,TagName from (select @rowNum:= 0) c,
		(select TagName,SUM( CONVERT(T2.TagCount,DECIMAL)) AS TagSum from MyTrackTagCount T2 
		where TrackID IN {$tid1}
		GROUP BY TagName
		order by TagSum desc limit 5) AS D) AS D1  natural join MyTrackTagCount M natural join MyTrackInformation
		where  ROWNUM = 1
		order by TagName,TagCount desc limit 20 ) 
		
		UNION

		(select distinct * from
		(select  @rowNum :=  if(@rowNum = 5, 0, @rowNum +1) as ROWNUM,TagName from
		(select TagName,SUM( CONVERT(T2.TagCount,DECIMAL)) AS TagSum from MyTrackTagCount T2 
		where TrackID IN {$tid1}
		GROUP BY TagName
		order by TagSum desc limit 5) AS D) AS D1  natural join MyTrackTagCount M natural join MyTrackInformation
		where  ROWNUM = 2
		order by TagName,TagCount desc limit 20 ) 

		UNION

		(select distinct * from
		(select  @rowNum :=  if(@rowNum = 5, 0, @rowNum +1) as ROWNUM,TagName from
		(select TagName,SUM( CONVERT(T2.TagCount,DECIMAL)) AS TagSum from MyTrackTagCount T2 
		where TrackID IN {$tid1}
		GROUP BY TagName
		order by TagSum desc limit 5) AS D) AS D1  natural join MyTrackTagCount M natural join MyTrackInformation
		where  ROWNUM = 3
		order by TagName,TagCount desc  limit 20)

		UNION

		(select distinct * from
		(select  @rowNum :=  if(@rowNum = 5, 0, @rowNum +1) as ROWNUM,TagName from
		(select TagName,SUM( CONVERT(T2.TagCount,DECIMAL)) AS TagSum from MyTrackTagCount T2 
		where TrackID IN {$tid1}
		GROUP BY TagName
		order by TagSum desc limit 5) AS D) AS D1  natural join MyTrackTagCount M natural join MyTrackInformation
		where  ROWNUM = 4
		order by TagName,TagCount desc limit 20)

		UNION

		(select  distinct * from
		(select  @rowNum :=  if(@rowNum = 5, 0, @rowNum +1) as ROWNUM,TagName from
		(select TagName,SUM( CONVERT(T2.TagCount,DECIMAL)) AS TagSum from MyTrackTagCount T2 
		where TrackID IN {$tid1}
		GROUP BY TagName
		order by TagSum desc limit 5) AS D) AS D1  natural join MyTrackTagCount M natural join MyTrackInformation
		where  ROWNUM = 5
		order by TagName,TagCount desc limit 20)
		) AS D10";
		//echo $query2;
		 $result3 = mysql_query($query2);
  echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
  while ($row2 = mysql_fetch_array($result3)) {
	 	
	 		echo "<tr>";
	 		echo "<td>Artist:{$row2{'TrackArtistName'}}</td>";
			echo "<td>Title:{$row2{'TrackTitle'}}</td>";
			echo "</tr>\n";

		}
		echo "</tr>\n";
		

}
}


}

   ?>
