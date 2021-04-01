<!DOCTYPE html>

<html>
  <head>
     <title>Youtube Downloader</title>
     <?php
         function getQualityFormatString($quality)
         {
            $qualityStr = "";

            switch ($quality)
            {
               case "mp3":
                  $qualityStr = ' -x --audio-format mp3 ';
                  break;
               case "mp4":
                  $qualityStr = ' -f mp4 ';
                  break;
               case "h144":
                  $qualityStr = ' -f "bestvideo[height<=144]+bestaudio/best[height<=144]" ';
                  break;
               case "h240":
                  $qualityStr = ' -f "bestvideo[height<=240]+bestaudio/best[height<=240]" ';
                  break;
               case "h360":
                  $qualityStr = ' -f "bestvideo[height<=360]+bestaudio/best[height<=360]" ';
                  break;
               case "h480":
                  $qualityStr = ' -f "bestvideo[height<=480]+bestaudio/best[height<=480]" ';
                  break;
               case "h720":
                  $qualityStr = ' -f "bestvideo[height<=720]+bestaudio/best[height<=720]" ';
                  break;
               case "h1080":
                  $qualityStr = ' -f "bestvideo[height<=1080]+bestaudio/best[height<=1080]" ';
                  break;
               case "default":
                  break;
            }

            return $qualityStr;
         }

         // if it's triggered by the "Download" button
         // echo print_r($_POST) . "<br/>";
         // echo print_r($_SERVER) . "<br/>";
         $THIS_SCRIPT = $_SERVER["SCRIPT_FILENAME"];

         if ( isset($_POST['submit']) )
         {
            $ytURL = $_POST['yturl'];
            $selectedFileExt = $_POST['fileExt'];
            $selectedQuality = $_POST['video_quality'];
            $outputDir = dirname( $THIS_SCRIPT );

            if (isset($_GET('ext'))) // URL overrides
            {
               $selectedFileExt = $_GET('ext');
            }

            if (isset($_GET('quality'))) // URL overrides
            {
               $selectedQuality = $_GET('quality');
            }

            $YTD_EXE="umask 000 ; LANG=en_US.UTF-8 youtube-dl ";

            ## for heroku app
            if ( file_exists('/app/youtube-dl') )
            {
               $YTD_EXE="umask 000 ; LANG=en_US.UTF-8 python /app/youtube-dl";
            }

            $qualityStr = getQualityFormatString($selectedQuality);

            $shellcmd = $YTD_EXE . ' -c ' . $qualityStr . ' -o "' .
                        $outputDir . '/%(title)s_%(id)s.%(ext)s" ' . $ytURL;

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
               <td>
                  Video Quality:
               </td>
               <td align="right">
                  <select name="video_quality" style="font-size: 0.7em">
                     <option value="default">default</option>
                     <option value="mp3">mp3</option>
                     <option value="mp4">mp4</option>
                     <option value="h144">Height <= 144</option>
                     <option value="h240">Height <= 240</option>
                     <option value="h360">Height <= 360</option>
                     <option value="h480">Height <= 480</option>
                     <option value="h720">Height <= 720</option>
                     <option value="h1080">Height <= 1080</option>
                  </select>
               </td>
            </tr>

            <tr>
                  <td colspan="2" align="right">
                        <input type="submit" name="submit" Value="Download"
                           style="font-size: 0.7em"/>
                  </td>
                  <td  align="left">
                      <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']) ?>">One level Up</a>
                  </td>
            </tr>
            </form>
         </table>
      </div>
  </body>

</html>
