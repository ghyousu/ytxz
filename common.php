<?php

function printDebug($str)
{
   $debug=0;

   if ($debug == 1)
   {
      echo "Debug: $str <br/>";
   }
}

// use public schema for now, can be renamed to other names if needed
function getSchemaName() { return 'ytxz'; }

// ytxz schema tables
function getUserTypeEnumTableName() { return getSchemaName() . ".user_type_enum"; }
function getFileTypeEnumTableName() { return getSchemaName() . ".file_type_enum"; }
function getQualityEnumTableName()  { return getSchemaName() . ".quality_enum"; }

function getUsersTableName()    { return getSchemaName() . ".users"; }
function getHistoryTableName()  { return getSchemaName() . ".history"; }
function getFavoriteTableName() { return getSchemaName() . ".favorite"; }

// session variable keys
function getStartDateSessionKey()       { return 'filter_date_start'; }
function getStopDateSessionKey()        { return 'filter_date_stop'; }
function getClassFilterSessionKey()     { return 'filter_class_id'; }
function getBreakTypeFilterSessionKey() { return 'filter_break_type'; }
function getFNameFilterSessionKey()     { return 'filter_fname'; }
function getLNameFilterSessionKey()     { return 'filter_lname'; }
function getDurationFilterSessionKey()  { return 'filter_duration'; }

// return a working connection, caller is responsible to close
// connection when done
function getDBConnection()
{
   $db_url = getenv("DATABASE_URL");

   if ($db_url == "") { die("Unable to get database URL!"); }

   $conn = pg_connect($db_url);

   if ($conn)
   {
      return $conn;
   }
   else
   {
      die("Failed to connect to database<br/>");
   }
}

function fetchQueryResults($query)
{
   $conn = getDBConnection();

   printDebug("query: '$query'<br/>");
   $result = pg_query($conn, $query);

   if ($result == false)
   {
      pg_close($conn);
      die('Failed to query from database');
   }

   pg_close($conn);
   return $result;
}

function authenticateUser($username, $pw)
{
   $query = "SELECT u.fname,u.lname,r.enum_id FROM " .
            getUsersTableName() . ' u, ' . getUserTypeEnumTableName() . ' r ' .
            " WHERE user_name = '$username' AND passwd = '" . sha1($pw) . "' AND " .
            'u.role_id = r.enum_id';

   printDebug("query: |$query|");

   $result = fetchQueryResults($query);

   if ($result == false)
   {
      die("Failed to get user info from database <br/>");
   }
   else
   {
      $row   = pg_fetch_row($result);
      $fname = $row[0];
      $lname = $row[1];
      $role  = $row[2];

      $_SESSION['user_role'] = $role;
      $_SESSION['fname'] = $fname;
      $_SESSION['lname'] = $lname;

      if ($role == '')
      {
         return false; // failed to find login into
      }

      return true;
   }
}

function displayStudentNamesFromDB($class)
{
   $NUM_COLUMNS = getMaxColumns();
   printDebug("NUM_COLUMNS = $NUM_COLUMNS <br/>");

   $query = "SELECT s.student_id, s.fname, s.lname, t.row, t.col FROM " .
            getStudentTableName() . " s, " .
            getSeatingTableName() . " t " .
            "WHERE s.class = '$class' AND s.student_id = t.student_id " .
            "ORDER BY t.row, t.col";

   $students = fetchQueryResults($query);

   echo "\n<table border='1' class='studentNamesTable'>\n";

   $html_string_array = array();

   $tr_idx = 0;
   $tc_idx = 1;
   $tr_data = "";
   while ( $student = pg_fetch_row($students) )
   {
      $row_fully_closed = false;
      $id = $student[0];

      // $name = $student[1] . "<br/>" . $student[2];
      $name = $student[1] . " " . substr($student[2], 0, 1) . ".";
      // $name = $student[1];

      $db_row = $student[3];
      $db_col = $student[4];

      printDebug("id: $id, name: '$name', row: '$db_row', col: '$db_col'");

      // open a new table row
      if ($tr_idx != $db_row)
      {
         if ($tr_data != "")
         {
            $tr_data = $tr_data . "</tr>\n";
            array_push($html_string_array, $tr_data);
         }

         $tr_data = "<tr>\n"; // new table row data

         $tr_idx = $db_row;
         $tc_idx = 1;
      }

      // empty seat, fill in a blank cell
      while ($tc_idx != $db_col)
      {
         $tr_data = $tr_data . "<td/>\n";
         $tc_idx += 1;
      }

      $html_input_prefix = "<input type='radio' name='student_id' ";
      $html_input_id = getStudentNameChkboxHtmlId($id);

      $tr_data = $tr_data . "<td id='td_label_" . $id . "' style='padding-bottom: 30px; padding-right: 30px;'>\n";
      $tr_data = $tr_data . "$html_input_prefix id='$html_input_id' value='$id' onchange='studentNameSelected(this)' />\n";
      $tr_data = $tr_data . "<label style='font-size: 1.5em' for='$html_input_id'><br/>$name</label>\n";
      $tr_data = $tr_data . "</td>\n";

      $tc_idx += 1;

      // last column of the row, close the table row
      if ( $db_col == $NUM_COLUMNS )
      {
         $tr_data = $tr_data . "</tr>\n";

         array_push($html_string_array, $tr_data);

         $tr_data = "";
         $tr_idx = $db_row;
         $tc_idx = 1;
      }
   }

   // if last row doesn't have enough columns, close the tr tag
   if ($tr_data != "")
   {
      $tr_data = $tr_data . "</tr>\n";

      array_push($html_string_array, $tr_data);
   }

   $reversed_array = array_reverse($html_string_array);

   for ($i=0; $i<count($reversed_array); ++$i)
   {
      echo $reversed_array[$i];
   }

   echo "</table>\n";
}

function displayBreakHistory($class)
{
   $tz = 'America/New_York';

   $is_teacher_account = ($_SESSION['user_role'] == 'teacher');

   $COLUMNS = "b.break_id, b.student_id, s.fname, s.lname, b.break_type, b.pass_type, " .
              "TO_CHAR(timezone('$tz', b.time_out), 'HH12:MI:SS AM'), " .
              "TO_CHAR(timezone('$tz', b.time_in),  'HH12:MI:SS AM'), " .
              "TO_CHAR(age(b.time_in, b.time_out), 'HH24:MI:SS') AS duration";

   if ($is_teacher_account)
   {
      $COLUMNS = $COLUMNS .
                 ", TO_CHAR(timezone('$tz', b.time_in),  'mm/DD/YYYY')" .
                 ", TO_CHAR(timezone('$tz', b.time_in),  'Dy')" .
                 ", s.class";
   }

   $HISTORY_QUERY = "SELECT $COLUMNS FROM " . getBreaksTableName() . " b, " .
                    getStudentTableName() . " s WHERE " . " b.student_id = s.student_id ";

   if ($is_teacher_account)
   {
      $start_date_str = $_SESSION[getStartDateSessionKey()];
      $stop_date_str  = $_SESSION[getStopDateSessionKey()];

      $HISTORY_QUERY = $HISTORY_QUERY .
         " AND DATE(b.time_out AT TIME ZONE '$tz')::date >= '$start_date_str' " .
         " AND DATE(b.time_out AT TIME ZONE '$tz')::date <= '$stop_date_str' " .
         getFilteringClause();
   }
   else
   {
      // class filter
      $HISTORY_QUERY = $HISTORY_QUERY . " AND s.class = '" . $_SESSION['class_id'] . "' ";

      // show TODAY filter
      $HISTORY_QUERY = $HISTORY_QUERY . "AND DATE(b.time_out AT TIME ZONE '$tz') = DATE(now() AT TIME ZONE '$tz')";
   }

   $HISTORY_QUERY = $HISTORY_QUERY . ' ORDER BY b.time_out';

   $entries = fetchQueryResults($HISTORY_QUERY);

   echo "<form action='/index.php' method='POST' enctype='multipart/form-data'>\n";
   echo "<table border=1>\n";

   if ($is_teacher_account)
   {
      echo "<th></th>\n"; // for checkbox
      echo "<th>Class</th>\n";
   }
   echo "<th>Name</th>\n";
   echo "<th>Break Type</th>\n";
   echo "<th>Pass</th>\n";
   if ($is_teacher_account)
   {
      echo "<th>Date</th>\n";
      echo "<th>Day</th>\n";
   }
   echo "<th>Time Out</th>\n";
   echo "<th>Time In</th>\n";
   echo "<th>Duration</th>\n";

   $hidden_html_ids = "0"; // prefix with an invalid ID

   $row_number = 1;
   while ( $entry = pg_fetch_row($entries) )
   {
      $break_id   = $entry[0];
      $id         = $entry[1];
      $fname      = $entry[2];
      $lname      = $entry[3];
      $break_type = $entry[4];
      $pass_type  = $entry[5];
      $time_out   = $entry[6];
      $time_in    = $entry[7];
      $durationHms= $entry[8];
      $date       = $entry[9];
      $day        = $entry[10];
      $class_id   = $entry[11];

      $uniq_id = $break_id . '@' . $id;

      if ($time_out == $time_in)
      {
         $hidden_html_ids = $hidden_html_ids . "_" . $uniq_id;

         $break_id_session_key = getBreakIdSessionKey($id);
         $_SESSION[$break_id_session_key] = $break_id;

         $time_in = "NA";
      }

      echo "\t<tr>\n";

      if ($is_teacher_account)
      {
         echo "\t\t<td align='center'>\n" .
              $row_number .
              "\t\t\t<input  style='width: 30px; height: 30px' type='checkbox' " .
              "name='break_checkbox[]' value='" .  $break_id . "'>\n" .
              "\t\t</td>\n";

         echo "\t\t<td>$class_id</td>\n";

         $row_number = $row_number + 1;
      }
      echo "\t\t<td>$fname $lname</td>\n";
      echo "\t\t<td id='break_type_" . $id . "'>$break_type</td>\n";
      echo "\t\t<td style='text-align: center' id='pass_type_"  . $break_id . "'>$pass_type</td>\n";
      if ($is_teacher_account)
      {
         echo "\t\t<td>" . $date . "</td>\n";
         echo "\t\t<td>" . $day . "</td>\n";
      }
      echo "\t\t<td id='time_out_"   . $id . "'>$time_out</td>\n";
      echo "\t\t<td id='time_in_"    . $id . "'>$time_in</td>\n";
      echo "\t\t<td " . getDurationHtmlStyleBgcolor($durationHms) .
           " id='duration_" . $break_id . "'>" . getHmsForDisplay($durationHms) . "</td>\n";

      echo "\t</tr>\n";
   }

   // show delete button
   if ($is_teacher_account)
   {
      echo "<br/><br/>\n";
      echo "\t<tr>\n" .
         "\t\t<td column-span='2' rowspan='2'>\n" .
         "<br/>" .
         "\t\t\t" . '<input type="submit" style="font-size: 1.5em" name="submit" Value="Delete Selected"/>' . "\n" .
         "\t\t</td>\n" .
         "\t</tr>\n";
   }

   echo '<input type="hidden" id="' . getHiddenFieldId() . '" name="checkedout_ids" value="' . $hidden_html_ids . '">';

   echo "</table>\n";
   echo "</form>\n";
} // end of displayBreakHistory

function showFavoritesTable($start_date_str, $stop_date_str)
{
   $tz = 'America/New_York';
   $query = 'SELECT f.fid,f.name,f.url,f.folder_name,' .
            "TO_CHAR(timezone('$tz', f.time_added), 'mm/DD/YYYY HH12:MI:SS AM') " .
            'FROM ' . getFavoriteTableName() . ' f, ' .
            getUsersTableName() . ' u ' .
            " WHERE f.uid = u.uid AND u.user_name = '" . $_SESSION['user_name'] . "'";

   $fav_list = fetchQueryResults($query);

   echo '<div align="center">';
   echo "<form action='/favorite.php' method='POST' enctype='multipart/form-data'>\n";
   echo "<table border=1>\n";

   echo "<th></th>\n"; // for checkbox
   echo "<th style='width: 60px'>Title</th>\n";
   echo "<th style='width: 200px'>URL</th>\n";
   echo "<th style='width: 600px'>Date/Time added</th>\n";

   $row_number = 1;
   while ( $entry = pg_fetch_row($fav_list) )
   {
      $fid        = $entry[0];
      $title      = $entry[1];
      $url        = $entry[2];
      $dir_name   = $entry[3];
      $time_added = $entry[4];

      echo "\t<tr>\n";

      echo "\t\t<td align='center'>\n" .
           $row_number .
           "\t\t\t<input  style='width: 20px; height: 20px' type='checkbox' " .
           "name='fav_checkbox[]' value='" .  $fid . "'>\n" .
           "\t\t</td>\n";

      echo "\t\t<td style='text-align: center'>$title</td>\n";
      echo "\t\t<td style='text-align: center'>$url</td>\n";
      echo "\t\t<td>$time_added</td>\n";

      echo "\t</tr>\n";

      $row_number = $row_number + 1;
   }

   // show delete button
   echo "<br/><br/>\n";
   echo "\t<tr>\n" .
      "\t\t<td column-span='2' rowspan='2'>\n" .
      "<br/>" .
      "\t\t\t" . '<input type="submit" style="font-size: 1.5em" name="submit" Value="Delete Selected"/>' . "\n" .
      "\t\t</td>\n" .
      "\t</tr>\n";

   echo "</table>\n";
   echo "</form>\n";
   echo "</div>\n";
} // end of showFavoritesTable

function showEnumDropDown($db_enum_name, $label, $html_name, $html_id)
{
   echo "<label for='$html_id'>$label</label>\n";
   echo "<select name='" . $html_name . "' id='" . $html_id . "'>\n";
   echo "\t<option value='All'>All</option>\n";

   $enum_array = getEnumArray($db_enum_name);

   $num_enums = count($enum_array);
   for ($i=0; $i<$num_enums; ++$i)
   {
      echo "\t<option value='" . $enum_array[$i] . "'>" . $enum_array[$i] . "</option>\n";
   }

   echo "</select>\n";
}

?>
