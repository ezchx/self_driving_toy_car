<?

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$max_readings = $_POST['max_readings'];
$clear = $_POST['clear'];

// Initialize variables
$username = "";
$password = "";
$database = "";
$found = False;

$offset = 0;
if ($max_readings == "") {$max_readings = 0;}

$room = [[0,0,0,0,0],
         [0,1,1,1,0],
         [0,0,0,0,0]];


// Clear the sense database
if ($clear == "True") {
  mysql_connect("localhost",$username,$password);
  @mysql_select_db($database) or die( "Unable to select database");
  $query = "DELETE FROM sense";
  mysql_query($query);
  mysql_close();
}


// Loop until CarBot finds the goal
while ($found == False) {

  // Check the sense database for a new reading
  mysql_connect("localhost",$username,$password);
  @mysql_select_db($database) or die( "Unable to select database");
  $query = "SELECT `readings` FROM sense ORDER by `ref`";
  $result = mysql_query($query);
  mysql_close();
  
  $num_readings = mysql_num_rows($result);
  
  if ($num_readings == "") {
    // If no readings, set the move command to (I)nitialize
    mysql_connect("localhost",$username,$password);
    @mysql_select_db($database) or die( "Unable to select database");
    $query = "UPDATE move SET direction = 'I' WHERE ref = 0";
    mysql_query($query);
    mysql_close();
  } 

  // If new reading
  if ($num_readings > $max_readings) {
  
    $max_readings = $num_readings;
    $master_sensor_array = [];
    $master_move_array = [];

    // Process sensor readings
    for ($i = 0; $i < $num_readings; $i++) {
    
      $reading = mysql_result($result, $i);
      $reading = $reading . "~";
      
      if ($reading[0] == "L") {$offset += 2;}
      if ($reading[0] == "R") {$offset -= 2;}
      
      if ($reading[0] != "I") {
        if ($offset % 8 == 0) {array_push($master_move_array, [-1, 0]);} # N
        if ($offset % 8 == 2 || $offset % 8 == -6) {array_push($master_move_array, [0, -1]);} # W
        if ($offset % 8 == 4 || $offset % 8 == -4) {array_push($master_move_array, [1, 0]);} # S
        if ($offset % 8 == 6 || $offset % 8 == -2) {array_push($master_move_array, [0, 1]);} # E
      }
      
      $sensor_val = "";
      $sensor_array = [];

      for ($j = 2; $j < strlen($reading); $j++) {
      
        if ($reading[$j] != "~") {
          $sensor_val = $sensor_val . $reading[$j];
        } else {
          $sensor_to_int = intval($sensor_val);
          if ($sensor_to_int > 0 && $sensor_to_int < 27) {
            $sensor_to_int = 1;
          } else {
            $sensor_to_int = 0;
          }
          echo $sensor_to_int;
          array_push($sensor_array, $sensor_to_int);
          $sensor_val = "";
        }
      
      }
      echo "<br>";
      
      // Adjust sensor readings based on orientation
      if ($offset != 0) {
        for ($k = 0; $k < $offset; $k++) {
          $first_item = $sensor_array[0];
          array_shift($sensor_array);
          array_push($sensor_array, $first_item);     
        }
      }
      
      array_push($master_sensor_array, $sensor_array);
    
    }
    
    array_push($master_move_array, [0, 0]);
    
    
    // Feed sensor and move data into localize.py and retrieve probability array
    $python = shell_exec('/home/ezchecks/python27/Python-2.7.12/python localize.py ' . escapeshellarg(json_encode($master_sensor_array)) . ' ' . escapeshellarg(json_encode($master_move_array)));
    $result = json_decode($python, true);
    
    // Print probabilities and estimate location
    $highest_prob = 0;
    $location_est = [];

    for ($l = 0; $l < sizeof($result); $l++) {
      for ($m = 0; $m < sizeof($result[$l]); $m++) {
        echo number_format($result[$l][$m], 3) . " ";
        if ($result[$l][$m] > $highest_prob) {
          $highest_prob = $result[$l][$m];
          $location_est = [$l, $m];
        }
      }
      echo "<br>";
    }
    

    // Feed location estimate into search.py and retrieve path to goal
    $python = shell_exec('/home/ezchecks/python27/Python-2.7.12/python search.py ' . escapeshellarg(json_encode($location_est)));
    $result = json_decode($python, true);
    
    for ($l = 0; $l < sizeof($result); $l++) {
      for ($m = 0; $m < sizeof($result[$l]); $m++) {
        echo $result[$l][$m] . " ";
      }
      echo "<br>";
    }
    
    $search_result = $result[$location_est[0]][$location_est[1]];
    
    // If goal then exit
    if ($search_result == "*") {
      $found = True;
      echo "Found Goal!<br>";
    }
    
    // If not goal, provide next move command
    //$move_command = "";
    if ($search_result == "^" && $offset % 8 == 0) {$move_command = "S";}
    if ($search_result == "^" && ($offset % 8 == 2 || $offset % 8 == -6)) {$move_command = "R";}
    if ($search_result == "^" && ($offset % 8 == 6 || $offset % 8 == -2)) {$move_command = "L";}
    
    if ($search_result == ">" && $offset % 8 == 0) {$move_command = "R";}
    if ($search_result == ">" && ($offset % 8 == 4 || $offset % 8 == -4)) {$move_command = "L";}
    if ($search_result == ">" && ($offset % 8 == 6 || $offset % 8 == -2)) {$move_command = "S";}  
    
    if ($search_result == "v" && ($offset % 8 == 2 || $offset % 8 == -6)) {$move_command = "L";}
    if ($search_result == "v" && ($offset % 8 == 4 || $offset % 8 == -4)) {$move_command = "S";}
    if ($search_result == "v" && ($offset % 8 == 6 || $offset % 8 == -2)) {$move_command = "R";}
    
    if ($search_result == "<" && $offset % 8 == 0) {$move_command = "L";}
    if ($search_result == "<" && ($offset % 8 == 2 || $offset % 8 == -6)) {$move_command = "S";}
    if ($search_result == "<" && ($offset % 8 == 4 || $offset % 8 == -4)) {$move_command = "R";}
    
    if ($move_command == "") {$move_command = "S";}
    

    
    // Update move database
    mysql_connect("localhost",$username,$password);
    @mysql_select_db($database) or die( "Unable to select database");
    $query="UPDATE move SET direction = '$move_command' WHERE ref = 0";
    mysql_query($query);
    mysql_close();
    
  }
  
  echo "offset = " . $offset . "<br>";
  echo "readings = " . $max_readings . "<br><br>";
  //echo $move_command . "<br>";

  
}


?>
