<html>
  <head>
     <title>Favorite</title>

<?php
   require_once("common.php");

   setlocale(LC_ALL,'C.UTF-8');

   date_default_timezone_set('America/New_York');

   session_start();

//   function getStartDateFilterHtmlId() { return 'notes_date_range_start'; }
//   function getStopDateFilterHtmlId()  { return 'notes_date_range_stop'; }
//   function getClassFilterHtmlId()     { return 'class_drop_down'; }
//
//   function getStartDateFilterHtmlName() { return getStartDateFilterHtmlId(); }
//   function getStopDateFilterHtmlName()  { return getStopDateFilterHtmlId(); }
//   function getClassFilterHtmlName()     { return getClassFilterHtmlId(); }

   if (!isset($_SESSION['LOGGED_IN']))
   {
      header("location: /login.php");
   }

//   if ($_SERVER['REQUEST_METHOD'] === 'POST')
//   {
//      // var_dump($_POST);
//      // die("<br/>temp");
//      // sample output:
//      //    array(2) {
//      //       ["notes"]=> string(9) "asdfasdf"
//      //       ["submit_notes"]=> string(6) "Submit"
//      //    }
//
//      if (isset($_POST['note_checkbox']))
//      {
//         $note_id_list = $_POST['note_checkbox'];
//         deleteNotes($note_id_list);
//      }
//      else if (isset($_POST['apply_filter']))
//      {
//         // array(3) { ["date_range_start"]=> string(10) "2021-12-09" ["date_range_stop"]=> string(10) "2022-01-08" ["apply_filter"]=> string(12) "Apply Filter" }
//         $_SESSION[getNotesStartDateSessionKey()]   = $_POST[getStartDateFilterHtmlName()];
//         $_SESSION[getNotesStopDateSessionKey()]    = $_POST[getStopDateFilterHtmlName()];
//         $_SESSION[getNotesClassFilterSessionKey()] = $_POST[getClassFilterHtmlName()];
//
//         printDebug("filtered start date: " . $_SESSION[getNotesStartDateSessionKey()] );
//         printDebug("filtered stop date:  " . $_SESSION[getNotesStopDateSessionKey()] );
//         printDebug("filtered class id:   " . $_SESSION[getNotesClassFilterSessionKey()] );
//      }
//      else // assume it's note submission from the index page
//      {
//         $notes = $_POST['notes'];
//
//         enterNotesToDatabase($notes);
//
//         header("location: /index.php");
//      }
//   }
?>
  </head>

<body>
      <?php
         showFavoritesTable();
      ?>
</body>

</html>
