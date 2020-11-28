<html>
  <head>
     <title>Directory Listing</title>
     <?php
         function myExec($cmd)
         {
             $output = "NA_OUTPUT";
             $ret_err_code = "-12345";

             $lastLineOutput = exec($cmd, $output, $ret_err_code);

             // for debugging: echo "Cmd: $cmd<br/>";
             // for debugging: echo "Output: $outpu<br/>";
             // for debugging: echo "ret_err_code: $ret_err_code<br/>";
             // for debugging: echo "lastLineOutput: $lastLineOutput<br/>";
         }

         // if it's triggered by the "Delete Selected" button, perform
         // file deletion and return to the same page
         // echo print_r($_POST) . "<br/>";

         if ( isset($_POST['check_list']) )
         {
            $selectedArray = $_POST['check_list'];
            $numSelected   = count( $selectedArray );

            for ($i=0; $i<$numSelected; $i++)
            {
               $tbdFile = $selectedArray[$i];
               myExec( "rm -fv \"$tbdFile\" " );
            }

            // if (isset($_SERVER["HTTP_REFERER"]))
            // {
            //    header("Location: " . $_SERVER["HTTP_REFERER"]);
            // }
         }
     ?>
  </head>

  <body>
     <?php
        // var_dump( $_SERVER ); // debug
        // echo "Debug: " . print_r( $_SERVER ) . "<br/>";

        $documentRoot    = $_SERVER["DOCUMENT_ROOT"];

        $thisScriptLinux = $_SERVER["SCRIPT_FILENAME"];
        $thisScriptBase  = basename($_SERVER["SCRIPT_FILENAME"]);
        $thisDirLinux    = dirname($thisScriptLinux);

        $thisScriptWeb   = $_SERVER["SCRIPT_NAME"];
        $thisDirWeb      = dirname($thisScriptWeb);
        $parentDirWeb    = dirname($thisDirWeb);

        // open pointer to directory and read list of files
        $d = @dir($thisDirLinux) or die("Failed opening directory $thisDirLinux for reading");

        $files = [];
        while (false !== ($entry = $d->read()))
        {
           array_push( $files, $entry );
        }
        sort($files);
        $d->close();

        foreach ($files as $file)
        {
           // skip hidden files
           if ($file[0] == ".") continue;

           if ($file == $thisScriptBase) continue;

           // These are files used for heroku. don't display them
           if ($file == "readme") continue;
           if ($file == "vendor") continue;
           if ($file == "Procfile") continue;
           if ($file == "youtube-dl") continue;
           if ($file == "composer.json") continue;

           $htmlRefFilename = "$thisDirWeb/$file";

           if ( $thisDirWeb == "/" )
           {
              $htmlRefFilename = "/$file";
           }

           if (is_dir("$thisDirLinux/$file"))
           {
              $retval[] = array(
                    "name" => $htmlRefFilename,
                    // "type" => filetype("$thisDirLinux/$file"),
                    "size" => 0,
                    "lastmod" => filemtime("$thisDirLinux/$file")
                    );
           }
           elseif (is_readable("$thisDirLinux/$file"))
           {
              $retval[] = array(
                    "name" => $htmlRefFilename,
                    // "type" => mime_content_type("$thisDirLinux/$file"),
                    "size" => filesize("$thisDirLinux/$file"),
                    "lastmod" => filemtime("$thisDirLinux/$file")
                    );
           }
        }

        echo "<h1>Index of $thisDirWeb</h1>";
     ?>

      <table>
         <tr>
            <th valign="top"><img src="/icons/blank.gif" alt="[ICO]"></th>
            <th><a href="?C=D;O=A">Delete File</a></th>
            <th><a href="?C=N;O=A">Name</a></th>
            <th><a href="?C=M;O=A">Last modified</a></th>
            <th><a href="?C=S;O=A">Size</a></th>
         </tr>

         <tr><th colspan="5"><hr></th></tr>

         <tr>
            <td valign="top"><img src="/icons/back.gif" alt="[PARENTDIR]"></td>
            <td><a href="<?php echo $parentDirWeb ?>">Parent Directory</a></td>
            <td>&nbsp;</td>
            <td align="right">  - </td>
            <td>&nbsp;</td>
         </tr>

         <form action="<?php echo $_SERVER["REQUEST_URI"] ?>" method="POST">
         <?php
            // copied from http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
            function human_filesize($bytes, $decimals = 2)
            {
               $size = array('','K','M','G','T','P','E','Z','Y');
               // echo "size = strlen($bytes)<br/>";
               $factor = floor((strlen($bytes) - 1) / 3);
               return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
            }

            for ( $i = 0; $i < count($retval); ++$i )
            {
               // drupal.org/project/drupal/issues/278425
               // "basename" function is no locale safe
               $fileBasename = basename($retval[$i]["name"]);

               preg_replace(".*/", "", $fileBasename);

               echo "<tr>\n";
               echo " <td></td>\n"; // skip icons
               echo ' <td align="right">' . "\n";
               echo '   <input type="checkbox" name="check_list[]" value="';
               echo        "$documentRoot/" . $retval[$i]["name"] . '">' . "\n";
               echo " </td>\n";
               echo " <td>\n";
               echo '   <a href="' . $retval[$i]["name"] . '">' . $fileBasename . "</a>\n";
               echo " </td>\n";
               echo ' <td align="right">' . date('Y-m-d h:i', $retval[$i]["lastmod"]) . "</td>\n";
               echo ' <td align="right">' . human_filesize($retval[$i]["size"]) . "</td>\n";
               echo "</tr>\n";
            }
         ?>

         <tr>
            <td colspan="2" align="right">
                  <input type="submit" name="submit" Value="Delete Selected"/>
            </td>
         </tr>
         </form>

         <tr><th colspan="5"><hr></th></tr>
      </table>

      <address>Mason's custom directory listing</address>

  </body>

</html>
