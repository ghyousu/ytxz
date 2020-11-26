<!DOCTYPE html>

<html>
  <head>
     <title>Youtube Downloader</title>
     <?php
         // if it's triggered by the "Download" button
         // echo print_r($_POST) . "<br/>";
         // echo print_r($_SERVER) . "<br/>";
         $THIS_SCRIPT = $_SERVER["SCRIPT_FILENAME"];

         if ( isset($_POST['submit']) )
         {
            $ytURL = $_POST['yturl'];
            $selectedFileExt = $_POST['fileExt'];
            $outputDir = dirname( $THIS_SCRIPT );

            $YTD_EXE="umask 000 ; LANG=en_US.UTF-8 youtube-dl";

            if ( get_current_user() == "ipj3ja1bmaxd")
            {
               $YTD_EXE="umask 000 ; LANG=en_US.UTF-8 ~/bin/youtube-dl";
            }

            $shellcmd = $YTD_EXE . ' -c -o "' . $outputDir . '/%(title)s_%(id)s.%(ext)s" ' . $ytURL;

            if ($selectedFileExt == "audio")
            {
               $shellcmd = $shellcmd . ' -f 140';
            }
            else if ($selectedFileExt == "video")
            {
               // do nothing
            }
            else
            {
               die("Unkown file extension: $selectedFileExt");
            }
            
            if (strpos($ytURL, 'playlist') !== false)
            {
            	$shellcmd = $shellcmd . ' --playlist-start ' . $_POST['plstart'] . ' --playlist-end ' . $_POST['plstop'];
            }

            // die("myou: debug: shellcmd = '" . $shellcmd . "'<br/>");

            // note: the escapedshellcmd adds bad character in "youtube-dl"
            //       options that messes up with the download
            exec( $shellcmd . ' > /dev/null &');
            echo "Download started, check back again later <br/>";
            /* download everything in the bg for all machines
            if ( get_current_user() != "ipj3ja1bmaxd" )
            {
               // for the slow machines
               exec( $shellcmd . ' > /dev/null &');
               echo "Download started, check back again later <br/>";
            }
            else
            {
               // for the fast machines
               exec( $shellcmd );

               // go to parent dir after finish downloading
               header("Location: " . dirname($_SERVER["SCRIPT_NAME"]) );
            }
            */
         }
     ?>
  </head>

  <body>
      <div align="center">
         <table style="font-size: 3em; border-spacing: 0.4em;">
            <form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="POST">
            <tr>
               <td>
                  Youtube Link:
               </td>
               <td align="right">
                  <input type="text" name="yturl" style="width: 400px; font-size: 0.5em" />
               </td>
            </tr>
            
            <tr>
            	<td>Playlist Start</td>
            	<td align="right">
                  <input type="text" value="1" name="plstart" style="width: 400px; font-size: 0.5em" />
               </td>
            </tr>
            <tr>
            	<td>Playlist Stop</td>
            	<td align="right">
                  <input type="text" value="100" name="plstop" style="width: 400px; font-size: 0.5em" />
               </td>
            </tr>

            <tr>
               <td>
                  File Format:
               </td>
               <td align="right">
                  <select name="fileExt" style="font-size: 0.7em">
                     <option value="audio">Audio</option>
                     <option value="video">Video</option>
                  </select>
               </td>
            </tr>

            <tr>
                  <td  align="left">
                      <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']) ?>">One level Up</a>
                  </td>
                  <td colspan="2" align="right">
                        <input type="submit" name="submit" Value="Download"
                           style="font-size: 0.7em"/>
                  </td>
            </tr>
            </form>
         </table>
      </div>
  </body>

</html>
