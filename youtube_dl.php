<!DOCTYPE html>

<html>
  <head>
     <title>Youtube Downloader</title>
     <script type="text/javascript">
       function updateDropDownBasedOnURL()
       {
          const queryString = window.location.search;
          const urlParams = new URLSearchParams(queryString);

          if (urlParams.has('fileExt'))
          {
             const url_file_ext = urlParams.get('fileExt');

             console.log("debug: fileExt = " + url_file_ext);

             if (url_file_ext == "video") // default to audio
             {
                document.getElementById("ft_list").selectedIndex = 1; // mp4
             }

             fileTypeChanged(url_file_ext);
          }

          if (urlParams.has('video_quality'))
          {
             const quality = urlParams.get('video_quality');

             console.log("debug: video_quality = " + quality)

             switch (quality)
             {
               case "default":
                  document.getElementById("video_quality").selectedIndex = 0;
                  break;
               case "mp3":
                  document.getElementById("video_quality").selectedIndex = 1;
                  break;
               case "mp4":
                  document.getElementById("video_quality").selectedIndex = 2;
                  break;
               case "h144":
                  document.getElementById("video_quality").selectedIndex = 3;
                  break;
               case "h240":
                  document.getElementById("video_quality").selectedIndex = 4;
                  break;
               case "h360":
                  document.getElementById("video_quality").selectedIndex = 5;
                  break;
               case "h480":
                  document.getElementById("video_quality").selectedIndex = 6;
                  break;
               case "h720":
                  document.getElementById("video_quality").selectedIndex = 7;
                  break;
               case "h1080":
                  document.getElementById("video_quality").selectedIndex = 8;
                  break;
               default:
                  document.getElementById("video_quality").selectedIndex = 0;
                  break;
             }
          }
       }

       function plStartChanged(e) // update plstop to match plstart
       {
          var plStartVal = parseInt(document.getElementsByName("plstart")[0].value);
          var plStopVal  = parseInt(document.getElementsByName("plstop")[0].value);

          if (plStartVal > plStopVal)
          {
             console.log("debug: changing stop from " + plStopVal + " to " + plStartVal);
             document.getElementsByName("plstop")[0].value = plStartVal;
          }
       }

       function fileTypeChanged(value) // if file type changed to video, default to mp4 in quality list
       {
          var selected_ft = document.getElementsByName("fileExt")[0].innerHTML;

          if (value == "video")
          {
             document.getElementById("video_quality").selectedIndex = 2; // mp4
          }
          else
          {
             document.getElementById("video_quality").selectedIndex = 0;
          }
       }
     </script>
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

         function getPlaylistTitle($pl_url, $debug)
         {
            $output = null;
            $retval = null;
            $shellcmd = 'LANG=en_US.UTF-8 python /app/youtube-dl ' .
                  $pl_url . ' --playlist-end 1 -O "%(playlist_title)s"';

            if ($debug)
            {
               echo "cmd: '" . $shellcmd . "' <br/>";
            }

            echo "before getPlaylistTitle = " . time() . "<br/>";
            exec( $shellcmd . ' 2> /dev/null', $output, $retval );
            echo "after getPlaylistTitle = " . time() . "<br/>";

            if ($debug)
            {
               print_r($output);
               echo "', retval = '" . $retval . "'<br/>";
            }

            if ($output == null)
            {
               return "";
            }
            else
            {
               // the output is an array
               return $output[0];
            }
         }

         // if it's triggered by the "Download" button
         // echo print_r($_POST) . "<br/>";
         // echo print_r($_SERVER) . "<br/>";
         $THIS_SCRIPT = $_SERVER["SCRIPT_FILENAME"];

         if ( isset($_POST['submit']) )
         {
            $debug = $_POST['debug'];
            $output_filename = '%(title)s_%(id)s.%(ext)s';
            $ytURL           = $_POST['yturl'];
            $selectedFileExt = $_POST['fileExt'];
            $selectedQuality = $_POST['video_quality'];
            $outputDir = dirname( $THIS_SCRIPT );

            $is_playlist = false;
            if (strpos($ytURL, 'playlist') != false)
            {
               $is_playlist = true;

               if ($_POST['output_name'] != "") // output filename overrides
               {
                  $outputDir = $outputDir . "/" . $_POST['output_name'];
               }
               else
               {
                  $pl_title = getPlaylistTitle($ytURL, $debug);
                  $outputDir = $outputDir . "/" . $pl_title;
               }

               $mkdir_cmd = 'mkdir -pv ' . $outputDir . ' && cp -v *.php ' . $outputDir;
               echo "before mkdir = " . time() . "<br/>";
               exec($mkdir_cmd . ' 2> /dev/null');
               echo "after mkdir = " . time() . "<br/>";
            }

//            if (isset($_GET['ext'])) // ext overrides
//            {
//               $selectedFileExt = $_GET['ext'];
//            }
//
//            if (isset($_GET['quality'])) // quality overrides
//            {
//               $selectedQuality = $_GET['quality'];
//            }

            if (isset($_GET['debug'])) // debug
            {
               $debug = true;
            }

            if (!$is_playlist)
            {
               if ($_POST['output_name'] != "") // output filename overrides
               {
                  $output_filename = $_POST['output_name'] . '_%(id)s.%(ext)s';
               }
            }

            // $ext_dl_opt = ' --external-downloader aria2c --external-downloader-args "-j 16 -x 16 -s 16 -k 1M" ';
            $ext_dl_opt = ''; ## not needed for yt-dlp

            $YTD_EXE="umask 000 ; LANG=en_US.UTF-8 youtube-dl ";

            ## for heroku app
            if ( file_exists('/app/youtube-dl') )
            {
               $YTD_EXE="umask 000 ; LANG=en_US.UTF-8 python /app/youtube-dl ";
            }

            $qualityStr = getQualityFormatString($selectedQuality);

            $shellcmd = $YTD_EXE . ' -c ' . $qualityStr . ' -o "' .
                        $outputDir . '/' . $output_filename . '" ' . $ext_dl_opt . $ytURL;

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

            if ($is_playlist)
            {
            	$shellcmd = $shellcmd . ' --playlist-start ' . $_POST['plstart'] .
                           ' --playlist-end ' . $_POST['plstop'] . ' &';
            }

            // die("myou: debug: shellcmd = '" . $shellcmd . "'<br/>");

            // note: the escapedshellcmd adds bad character in "youtube-dl"
            //       options that messes up with the download
            if ($debug)
            {
              $output = null;
              $retval = null;
              exec( $shellcmd . ' 2>&1', $output, $retval );
              echo "<br/>retval = " . $retval . ". output: <br/>";
              print_r($output);
              die("<br/>end of page");
            }
            else
            {
              echo "before ytd = " . time() . "<br/>";
              exec($shellcmd . ' > /dev/null &');
              echo "after ytd = " . time() . "<br/>";
              echo "Download started, check back again later <br/>";
            }

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

  <body onload="updateDropDownBasedOnURL()">
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
            	<td>Playlist Start:</td>
            	<td align="right">
                  <input type="text" value="1" name="plstart" onchange="plStartChanged(event)" style="width: 400px; font-size: 0.5em" />
              </td>
            </tr>
            <tr>
            	<td>Playlist Stop:</td>
            	<td align="right">
                  <input type="text" value="100" name="plstop" style="width: 400px; font-size: 0.5em" />
              </td>
            </tr>
            <tr>
            	<td>Output name:</td>
            	<td align="right">
                  <input type="text" value="" name="output_name" style="width: 400px; font-size: 0.5em" />
              </td>
            </tr>

            <tr>
               <td>
                  File Format:
               </td>
               <td align="right">
                  <select id="ft_list" name="fileExt" onchange="fileTypeChanged(this.value)" style="font-size: 0.7em">
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
                  <select id="video_quality" name="video_quality" style="font-size: 0.7em">
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
                  <td align="left">
                      <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']) ?>">One level Up</a>
                  </td>
                  <td align="right">
                      <input type="submit" name="submit" Value="Download"
                           style="font-size: 0.7em"/>
                  </td>
                  <td align="left">
                      <input type="checkbox" name="debug" value="Debug" />
                  </td>
            </tr>
            </form>
         </table>
      </div>
  </body>

</html>
