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
	session_start();
?> 
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
define("DB_SERVER", "localhost");
define("DB_USER", "root");
define("DB_PASS", "ukoolz");
define("DB_NAME", "toy");
define("DB_SERVER1", "localhost");
define("DB_USER1", "root");
define("DB_PASS1", "ukoolz");
define("DB_NAME1", "musicdb");
	$servername = "localhost";
	$username = "root";
	$password = "ukoolz";
	$dbname = "mydb";
	$tracktable="MyTrackInformation";
	$tagtable="MyTrackTagCount";
	$similar="MyTrackSimilar";
	$artist="MyArtistInformation";
	$group="MyTrackGroup";
 $searchcover = 0;
 $sc=0;
		
		if(isset($_POST['searchcover'])) {
			$sc = $_POST['searchcover'];
			echo "<br/>Search Cover:{$sc}";
		}
//Create a database connection
//Create a database connection

	$connect = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if(!$connect) {
		die("Failed to connect to the database" . mysql_error());
		}
		$connect1 = mysql_connect(DB_SERVER1, DB_USER1, DB_PASS1);
	if(!$connect1) {
		die("Failed to connect to the database" . mysql_error());
		}
	//select database
	
	$db_select = mysql_select_db(DB_NAME, $connect);
	
	if(!$db_select) {
		die("Failed to select database" . mysql_error());
		}

	/**/
   /* $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS,DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }*/
    echo "Connected successfully";
    	
		
		  
if(isset($_POST['dropdown'])) {
			$dd = $_POST['dropdown'];
		}
		if(isset($_POST['option']))  {
			/*check whether union (OR) or intersection (AND)*/
			$myopt = $_POST['option'];
		}
		if((strlen($_POST['searchstr']) !=0)&&isset($_POST['searchstr'])) 
		{
		$totalstring = $_POST['searchstr'];}
	?>
	
	

<table id ="structure">

	<tr>
		<td id ="nav">
		<ul class ="sub">
			<form action="filter1.php?searchstr=<?php global $totalstring; echo urlencode($totalstring);?>&option=<?php global $myopt; echo urlencode($myopt);?>&dropdown=<?php global $dd; echo urlencode($dd);?>&searchcover=<?php global $sc; echo urlencode($sc); ?>" method="post">
				<div style="padding:-10 0 0 0 !important;text-size: 26;"><center><h2>FILTERS</h2></center></div><br/>
			<?php
					global $connect;
			if(isset($_POST['dropdown'])) {
			$dd = $_POST['dropdown'];
		}
		
  
 		if((strlen($_POST['searchstr']) !=0)&&isset($_POST['searchstr'])) 
		{
		$totalstring = $_POST['searchstr'];
		$stringarr = explode(",", $totalstring);
	   $sQuery ="SELECT * FROM ";
  		if(isset($_POST['option']))  {
			/*check whether union (OR) or intersection (AND)*/
			$myopt = $_POST['option'];
		}
	
		if($dd == "artist" || $dd == "title")
				{
					$temp="";
			/*search by artist or title in table 'tracks'*/
			$sQuery.= "{$tracktable} WHERE ";
			if($dd == "artist"){
				foreach($stringarr as $s=>$i){
					/*OR each artist*/
					$sQuery.="TrackArtistName LIKE \"%" . trim($i) . "%\" {$myopt} ";			
				}
				$sQuery = chop($sQuery, " {$myopt} ");
				$temp=$sQuery;	
				$sQuery.=";";	
					
				//echo $sQuery;
			}else
			 {
				foreach($stringarr as $s=>$i)
				{
					/*OR each title*/
					$sQuery.="TrackTitle LIKE \"%" . trim($i) . "%\" {$myopt} ";			
				}
				$sQuery = chop($sQuery, " {$myopt} ");
				$temp=$sQuery;
				$sQuery.=";";		
			}
			
			//echo $sQuery . "<br/>";	
			$query1="CREATE TEMPORARY TABLE initial (";
			$query1=$query1.$temp.");";
			//echo $query1;
		
				//confirm_query($intarr);
				//echo "cleared intarr<br/>";
			
			
			
					


$q="select * from MyArtistInformation limit 20 ;";
$r="select TrackId,TagName,count(TagName) as cnt from MyTrackTagCount where
 TrackId 
 in('TRAAAAK128F9318786','TRAAABD128F429CF47') group by TrackId having cnt>0 order by cnt DESC LIMIT 0,15;";

	 	//$result=$conn->query(sprintf("select * from artist limit 20;"));
	 //	echo $q;
	 	$table="search information";
	 	$result = mysql_query($sQuery);
	 	echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
	 	while ($row = mysql_fetch_array($result)) {
	 		
	 	
	 		 echo "<tr>";
	 		echo "<td>ID:{$row{'TrackID'}}</td>";
			echo "<td>Artist:{$row{'TrackArtistName'}}</td>";
			echo "<td>title:{$row{'TrackTitle'}}</td>";
			echo "<td>year:{$row{'Year'}}</td>";
			echo "</tr>\n";
					
	 		echo "Name:".$row{'TrackArtistName'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "tags:".$row{'TrackTitle'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "year:".$row{'Year'};
	 		echo "</td>";
	 		echo "</tr><br>";
   //echo "ID:".$row{'TrackID'}."Name:".$row{'TrackArtistName'}."year:".$row{'Year'}."tags:".$row{'TrackTitle'}."<br>";
	//	}
	echo "</tr>\n";
   

		}
		if($dd == "tag")
		{
if($myopt == "AND") {
			/*search by tag*/
		
				$counter =0;
				$temp="CREATE TEMPORARY TABLE initial (";
				
				/*select t1.trackId from ((SELECT DISTINCT trackId FROM tags WHERE tagName LIKE "%{tag1}%") UNION ALL (SELECT DISTINCT trackId FROM tags WHERE tagName = \"%{tag2}%")) 
				AS t1 GROUP BY trackId HAVING COUNT(*) >=no of tags searched*/
				$sQuery="SELECT * FROM (";
				foreach($stringarr as $i=>$v)
				{
					/*UNION ALL each tag*/
					++$counter;
					$sQuery.="(SELECT DISTINCT * FROM {$tagtable} WHERE TagName LIKE \"%" . trim($v) . "%\") UNION ALL ";			
				}
				$sQuery = chop($sQuery, " UNION ALL ");
				$sQuery.=") AS t1 GROUP By TrackID HAVING COUNT(*) >= {$counter}";
				$query1=$temp.$sQuery.");";
				$sQuery=$sQuery.";";
				//echo $sQuery ."<br/>";
				$result = mysql_query($sQuery);
	 	echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
	 	while ($row = mysql_fetch_array($result)) {
	 		
	 	
	 		  echo "<tr>";
	 		echo "<td>ID:{$row{'TrackID'}}</td>";
			echo "<td>Tag:{$row{'TagName'}}</td>";
			echo "<td>Count:{$row{'TagCount'}}</td>";
			echo "</tr>\n";
					
	 		echo "Name:".$row{'TrackArtistName'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "tags:".$row{'TrackTitle'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "year:".$row{'Year'};
	 		echo "</td>";
	 		echo "</tr><br>";
   //echo "ID:".$row{'TrackID'}."Name:".$row{'TrackArtistName'}."year:".$row{'Year'}."tags:".$row{'TrackTitle'}."<br>";
		}
		echo "</tr>\n";
    
		}
		elseif($myopt == "OR") 
		{
				$temp="CREATE TEMPORARY TABLE initial (";
				$sQuery = "SELECT DISTINCT * FROM ";
				$sQuery.="{$tagtable} WHERE ";				
				
				foreach($stringarr as $i=>$d){
					/*OR each tag*/
					$sQuery.="TagName LIKE \"%" . trim($d) . "%\" {$myopt} ";			
				}
				$sQuery = chop($sQuery, " {$myopt} ");
				$query1=$temp.$sQuery.");";
				$sQuery=$sQuery.";";
				
				
		//		echo $sQuery . "<br/>";
				$result = mysql_query($sQuery);
	 	
	 	echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
	 	while ($row = mysql_fetch_array($result)) {
	 		
	 	
	 		 echo "<tr>";
	 		echo "<td>ID:{$row{'TrackID'}}</td>";
			echo "<td>Tag:{$row{'TagName'}}</td>";
			echo "<td>Count:{$row{'TagCount'}}</td>";
			echo "</tr>\n";
					
	 		echo "Name:".$row{'TrackArtistName'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "tags:".$row{'TrackTitle'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "year:".$row{'Year'};
	 		echo "</td>";
	 		echo "</tr><br>";
   //echo "ID:".$row{'TrackID'}."Name:".$row{'TrackArtistName'}."year:".$row{'Year'}."tags:".$row{'TrackTitle'}."<br>";
		}
		echo "</tr>\n";
    
				
		}
	
}	$var1 = mysql_query($query1, $connect);
$createtable = "CREATE TEMPORARY TABLE table2 (SELECT t1.TrackID, (SELECT COUNT(t2.TagName) FROM {$tagtable} AS t2 WHERE t1.TrackID = t2.TrackID) AS totaltrack, (SELECT t3.TrackTitle FROM {$tracktable} as t3 WHERE t1.TrackID = t3.TrackID) AS title, (SELECT t3.TrackArtistName FROM {$tracktable} as t3 WHERE t1.TrackID = t3.TrackID) AS artistName, (SELECT t3.Year FROM {$tracktable} AS t3 WHERE t1.TrackID=t3.TrackID) AS year FROM initial as t1 ORDER BY totaltrack DESC);";
	$var2 = mysql_query($createtable, $connect);
	
	$result1 = mysql_query("SELECT * FROM t2 LIMIT 0, 15;", $connect);
		  }
		  			$q5="SELECT MIN(year) AS minyear FROM table2;";
		  
					$yquery = mysql_query($q5, $connect);
					
					if($resultyear = mysql_fetch_array($yquery)){
						$minyear = $resultyear["minyear"];
						echo "MIN YEAR: {$minyear}";
						$q6="SELECT MAX(year) AS max_year FROM table2;";
						$year_query = mysql_query($q6, $connect);
						
						if($result_max_year = mysql_fetch_array($year_query)){
						$max_year = $result_max_year["max_year"];
						echo "MAX YEAR: {$max_year}" . "<br/>";
						
							$yearintv = floor(($max_year - $minyear+1)/5);
							
							echo "<br/><div style=\"padding:- 0 0 0 0 !important;text-size: 25;\"><b>YEAR</b></div><br/>";
							$lower_year = $minyear;
							
							if($max_year - $minyear >= 5)
							{
								for($year_box=1; $year_box<=5; $year_box++)
								{
									if($year_box==5){
										$upper_year = $max_year;
									}if($year_box!=5){			
										$upper_year = $lower_year+$yearintv-1;
									}
								
									echo "<input id=\"checkbox{$year_box}\" type=\"checkbox\" name=\"filteryear[]\" value=\"{$lower_year}-{$upper_year}\">";
									echo "<label for=\"checkbox{$year_box}\" style=\"font-size:17;\">{$lower_year} to {$upper_year}</label><br/>";
									$lower_year+=$yearintv;								
								}
							}
							if($max_year - $minyear < 5) {
								for($year_box=1; $year_box<=($max_year-$minyear)+1; $year_box++)
								{
									echo "<input id=\"checkbox{$year_box}\" type=\"checkbox\" name=\"filteryear[]\" value=\"{$lower_year}-{$lower_year}\">";
									echo "<label for=\"checkbox{$year_box}\" style=\"font-size:17;\">{$lower_year}</label><br/>";
									$lower_year+=1;								
								}		
							}
						}
					}
					
					$tagtablequery = "CREATE TEMPORARY TABLE taginitial1(SELECT t1.TagName, (SELECT COUNT(t2.TagName) FROM {$tagtable} as t2 WHERE t3.TrackID=t2.TrackID) AS count FROM {$tagtable} as t1, initial as t3 WHERE t3.TrackID=t1.TrackID);";
					
					$tagint = mysql_query($tagtablequery, $connect);
					
					
					$tagselectquery = "SELECT DISTINCT TagName FROM taginitial1 ORDER BY count DESC LIMIT 0, 5;";
					
					if($tagint)
					{
						$tagarr = mysql_query($tagselectquery, $connect);
						//confirm_query($tagarr);
						echo "<br/><div style=\"padding:- 0 0 0 0 !important;text-size: 17;\"><b>TAGS</b></div><br/>";
						
						$tagfilterbox =1;
						while($tagentry = mysql_fetch_array($tagarr))
						{
							echo "<input id=\"checkbox{$tagfilterbox}\" type=\"checkbox\" name=\"filtertag[]\" value=\"" . $tagentry["TagName"] . "\">";
							echo "<label for=\"checkbox{$tagfilterbox}\" style=\"font-size:14;\">" . $tagentry["TagName"] . "</label><br/>";
							++$tagfilterbox;
						}
					}
					echo "<br/>";
				 ?>
				 
				 
				<center><input type="submit" name="select" value="FILTER" style="width:230px; font-size:18;"/><br/></center>
			</form>
		</ul>
		</td>
		<td id = "page">
			<div style="padding:10px 10px 10px 0px;"><center><h1>RESULTS</h1></center></div>
			<form action="genplaylist.php" method="post">
				<div>
					
					
					<?php
							echo "<link rel=\"stylesheet\" href=\"css/style.css\">";
							echo "<link href='./images/favicon.ico' rel='icon' type='image/x-icon'/>";
							echo "<script src=\"js/jquery.min.js\"></script>";
					
						global $s;
					
						if(isset($_POST['dropdown'])) {
			$dd = $_POST['dropdown'];
		}
		
  
 	if((strlen($_POST['searchstr']) !=0)&&isset($_POST['searchstr'])) 
		{
		$totalstring = $_POST['searchstr'];
		$stringarr = explode(",", $totalstring);
	   $searchQ ="SELECT * FROM ";
  		if(isset($_POST['option']))  {
			/*check whether union (OR) or intersection (AND)*/
			$myopt = $_POST['option'];
		}
	
		if($dd == "artist" || $dd == "title")
				{
					$temporary="";
			/*search by artist or title in table 'tracks'*/
			$searchQ.= "{$tracktable} WHERE ";
			if($dd == "artist"){
				foreach($stringarr as $s=>$i){
					/*OR each artist*/
					$searchQ.="TrackArtistName LIKE \"%" . trim($i) . "%\" {$myopt} ";			
				}
				$searchQ = chop($searchQ, " {$myopt} ");
				$temporary=$searchQ;	
				$searchQ.=";";	
					
				//echo $searchQ;
			}else
			 {
				foreach($stringarr as $s=>$i)
				{
					/*OR each title*/
					$searchQ.="TrackTitle LIKE \"%" . trim($i) . "%\" {$myopt} ";			
				}
				$searchQ = chop($searchQ, " {$myopt} ");
				$temporary=$searchQ;
				$searchQ.=";";		
			}
			
			//echo $searchQ . "<br/>";	
			$qry1="CREATE TEMPORARY TABLE initial5 (";
			$qry1=$qry1.$temporary.");";
			//echo $qry1;
		
				//confirm_query($intarr);
				//echo "cleared intarr<br/>";
			
			
			
					


$q="select * from MyArtistInformation limit 20 ;";
$r="select TrackId,TagName,count(TagName) as cnt from MyTrackTagCount where
 TrackId 
 in('TRAAAAK128F9318786','TRAAABD128F429CF47') group by TrackId having cnt>0 order by cnt DESC LIMIT 0,15;";

	 	//$result=$conn->query(sprintf("select * from artist limit 20;"));
	 //	echo $q;
	 	$table="search information";
	 	$result = mysql_query($searchQ);
	 	echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
	 	while ($row = mysql_fetch_array($result)) {
	 		
	 	
	 		 echo "<tr>";
	 		echo "<td>ID:{$row{'TrackID'}}</td>";
			echo "<td>Artist:{$row{'TrackArtistName'}}</td>";
			echo "<td>title:{$row{'TrackTitle'}}</td>";
			echo "<td>year:{$row{'Year'}}</td>";
			echo "</tr>\n";
					
	 		echo "Name:".$row{'TrackArtistName'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "tags:".$row{'TrackTitle'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "year:".$row{'Year'};
	 		echo "</td>";
	 		echo "</tr><br>";
   //echo "ID:".$row{'TrackID'}."Name:".$row{'TrackArtistName'}."year:".$row{'Year'}."tags:".$row{'TrackTitle'}."<br>";
		}
		echo "</tr>\n";
   

		}
		if($dd == "tag")
		{
if($myopt == "AND") {
			/*search by tag*/
		
				$counter =0;
				$temporary="CREATE TEMPORARY TABLE initial5 (";
				
				/*select t1.trackId from ((SELECT DISTINCT trackId FROM tags WHERE tagName LIKE "%{tag1}%") UNION ALL (SELECT DISTINCT trackId FROM tags WHERE tagName = \"%{tag2}%")) 
				AS t1 GROUP BY trackId HAVING COUNT(*) >=no of tags searched*/
				$searchQ="SELECT * FROM (";
				foreach($stringarr as $i=>$v)
				{
					/*UNION ALL each tag*/
					++$counter;
					$searchQ.="(SELECT DISTINCT * FROM {$tagtable} WHERE TagName LIKE \"%" . trim($v) . "%\") UNION ALL ";			
				}
				$searchQ = chop($searchQ, " UNION ALL ");
				$searchQ.=") AS t1 GROUP By TrackID HAVING COUNT(*) >= {$counter}";
				$qry1=$temporary.$searchQ.");";
				$searchQ=$searchQ.";";
			//	echo $searchQ ."<br/>";
				$result = mysql_query($searchQ);
	 echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
	 	while ($row = mysql_fetch_array($result)) {
	 		
	 	
	 		  echo "<tr>";
	 		echo "<td>ID:{$row{'TrackID'}}</td>";
			echo "<td>Tag:{$row{'TagName'}}</td>";
			echo "<td>Count:{$row{'TagCount'}}</td>";
			echo "</tr>\n";
					
	 		echo "Name:".$row{'TrackArtistName'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "tags:".$row{'TrackTitle'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "year:".$row{'Year'};
	 		echo "</td>";
	 		echo "</tr><br>";
   //echo "ID:".$row{'TrackID'}."Name:".$row{'TrackArtistName'}."year:".$row{'Year'}."tags:".$row{'TrackTitle'}."<br>";
		}
		echo "</tr>\n";
    
		}
		elseif($myopt == "OR") 
		
		{		$qry1=null;
				$temporary="CREATE TEMPORARY TABLE initial5 (";
				$squery=null;
				$searchQ = "SELECT DISTINCT * FROM ";
				$searchQ.="{$tagtable} WHERE ";				
				
				foreach($stringarr as $i=>$d){
					/*OR each tag*/
					$searchQ.="TagName LIKE \"%" . trim($d) . "%\" {$myopt} ";			
				}
				$searchQ = chop($searchQ, " {$myopt} ");
				$qry1=$temporary.$searchQ.");";
				$searchQ=$searchQ.";";
				
				
				//echo $searchQ . "<br/>";
				$result = mysql_query($searchQ);
	 	
	 	echo "<h1>Table: {$table}</h1>";
      echo "<table border='1'><tr>";
	 	while ($row = mysql_fetch_array($result)) {
	 		
	 	
	 		 echo "<tr>";
	 		echo "<td>ID:{$row{'TrackID'}}</td>";
			echo "<td>Tag:{$row{'TagName'}}</td>";
			echo "<td>Count:{$row{'TagCount'}}</td>";
			echo "</tr>\n";
					
	 		echo "Name:".$row{'TrackArtistName'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "tags:".$row{'TrackTitle'};
	 		echo "</td>";
	 		echo "<td>";
	 		echo "year:".$row{'Year'};
	 		echo "</td>";
	 		echo "</tr><br>";
   //echo "ID:".$row{'TrackID'}."Name:".$row{'TrackArtistName'}."year:".$row{'Year'}."tags:".$row{'TrackTitle'}."<br>";
		}
		echo "</tr>\n";
    
				
		}
	
}	$var1 = mysql_query($qry1, $connect);
   $Query = "CREATE TABLE myinitial (SELECT initial.trackId, (SELECT groups.groupId FROM groups WHERE initial.trackId = groups.trackId) AS groupId, initial.alltags, initial.tagcount, (SELECT track.title FROM track WHERE track.trackId = initial.trackId) AS title, (SELECT track.artistId FROM track WHERE track.trackId = initial.trackId) AS allartists, (SELECT track.year FROM track WHERE track.trackId = initial.trackId) AS year FROM initial ORDER BY initial.tagcount DESC );";
   $createtable1 = "CREATE TEMPORARY TABLE table3 (SELECT t1.TrackID, (SELECT COUNT(t2.TagName) FROM {$tagtable} AS t2 WHERE t1.TrackID = t2.TrackID) AS totaltrack, (SELECT t3.TrackTitle FROM {$tracktable} as t3 WHERE t1.TrackID = t3.TrackID) AS title, (SELECT t3.TrackArtistName FROM {$tracktable} as t3 WHERE t1.TrackID = t3.TrackID) AS artistName, (SELECT t3.Year FROM {$tracktable} AS t3 WHERE t1.TrackID=t3.TrackID) AS year FROM initial5 as t1 ORDER BY totaltrack DESC);";
	$var2 = mysql_query($createtable1, $connect);
	//echo $createtable1;
	//echo "$qry1<br/>";
	$result2 = mysql_query("SELECT * FROM table3 LIMIT 0, 15;", $connect);
		  
		  
		}				global $result2;
						global $connect1;
						global $searchcover;
						//echo $result2;
						
						$num = mysql_num_rows($result2);
						//echo "me yha hu";
				if($sc==0){		
						$t_box = 1;
						$a_box = 1;
						while($resrow = mysql_fetch_array($result2)) {
							
							//$innerresult = mysql_query($qs, $connect);
							//confirm_query($innerresult);
							//echo "me yha hu";
							
							echo "<input id = \"checkbox{$t_box}\" type =\"checkbox\" name = \"chktitle[]\" value = \"" . $resrow["title"] . "\">";
							echo "<label for=\"checkbox{$t_box}\" style=\"font-size:19;\">" . $resrow["title"] . "</label><br/>";
							
							echo "<input id = \"checkbox{$a_box}\" type=\"checkbox\" name = \"chkartist[]\" value = \"" . $resrow["artistName"] . "\">";
							echo "<label for=\"checkbox{$a_box}\" style=\"font-size:17;\">" . $resrow["artistName"] . "</label><br/>"; 
							
							echo "&nbsp &nbsp &nbsp &nbsp<b style=\"font-size:15 !important;\">" . $resrow["year"] . "</b><br/>";
							++$t_box;
							++$a_box;
							
							$qat = "SELECT TagName from {$tagtable} WHERE TrackID = \"" . $resrow["TrackID"] . "\"";
							$tagresult1 = mysql_query($qat);
						
							$tag_box = 1;
							while($atom = mysql_fetch_array($tagresult1)) {
								echo "<input id = \"checkbox{$tag_box}\" type =\"checkbox\" name = \"chktag[]\" value = \"" . $atom["TagName"] . "\">";
								echo "<label for=\"checkbox{$tag_box}\" style=\"font-size:15;\">" . $atom["TagName"] . "</label>";
								++$tag_box;								
							}
							echo "Total tags: {$tag_box}<br/><br/>";
							
							
						}
				
				}else	{
							echo "hello me hu";
					$myq1="CREATE TEMPORARY TABLE covertable1 (SELECT t3.TrackID, t3. GroupID, GROUP_CONCAT(t3.similarTracks) AS allcovers, GROUP_CONCAT(t3.TagName) AS alltags, GROUP_CONCAT(MyTrackInformation.TrackArtistName) AS allartists FROM (SELECT t2.TrackID, t2.GroupID, t2.similarTracks, MyTrackTagCount.TagName FROM (SELECT t1.TrackID, t1.GroupID, MyTrackGroup.TrackID AS similarTracks FROM (SELECT initial5.TrackID, MyTrackGroup.GroupID FROM initial5 LEFT JOIN MyTrackGroup ON initial5.TrackID=MyTrackGroup.TrackID) AS t1 LEFT JOIN MyTrackGroup ON t1.GroupID= MyTrackGroup.GroupID LIMIT 0, 15)AS t2 LEFT JOIN MyTrackTagCount ON t2.similarTracks=MyTrackTagCount.TrackID) AS t3 LEFT JOIN MyTrackInformation ON t3.similarTracks=MyTrackInformation.TrackID GROUP BY t3.TrackID);";
					$intcover = mysql_query($myq1, $connect);
					//echo $myq1;
					
					$intcover = mysql_query("ALTER TABLE `covertable1` ADD `title` VARCHAR( 99 );", $connect);
				
		
			      //$intcover = mysql_query("UPDATE covertable1, initial5 SET covertable1.alltags=initial5.alltags, covertable1.allartists=initial5.allartists WHERE covertable1.groupId IS NULL AND covertable1.trackId=initial5.trackId;", $connect);
		
					$intcover = mysql_query("UPDATE covertable1, initial5 SET covertable1.title=initial5.TrackTitle WHERE covertable1.title IS NULL AND covertable1.TrackId=initial5.TrackID;", $connect);
				
					$resultarr= mysql_query("SELECT * FROM covertable1 LIMIT 0, 15;", $connect);
				
					
					$num = mysql_num_rows($resultarr);
					$tbox = 1;
					while($resultrow = mysql_fetch_array($resultarr)) {
						echo "<div>";
						echo "<input id = \"checkbox{$tbox}\" type =\"checkbox\" name = \"chktitle[]\" value = \"" . $resultrow["title"] . "\">";
						echo "<label for=\"checkbox{$tbox}\" style=\"font-size:20;\">" . $resultrow["title"] . "</label><br/>";
						echo "&nbsp &nbsp &nbsp &nbsp<b style=\"font-size:15 !important;\">" . $resultrow["TrackId"] . "</b><br/>";
						echo "&nbsp &nbsp &nbsp &nbsp<b style=\"font-size:15 !important;\">" . $resultrow["allartists"] . "</b><br/>";
						echo "&nbsp &nbsp &nbsp &nbsp<b style=\"font-size:12 !important;\">" . $resultrow["alltags"] . "</b><br/>";
						
						++$tbox;
						echo "</div>";
					}
				}
						if(!(isset($_POST['searchstr']) && (strlen($_POST['searchstr']) !=0))) 
						{
							echo "<b>No keywords to search</b>";
						}
						
					
					?>
					
				</div>
				<center><input type="submit" name="select" value="SELECT" style="width:230px; font-size:18;"/><br/></center>
			</form>
			<div>

			</div>

			<?php 
			?>
		</td>
	</tr>

</table>
						
