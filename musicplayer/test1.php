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