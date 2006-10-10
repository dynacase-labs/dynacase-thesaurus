<?php

    require_once "HTTP/WebDAV/Server.php";
    require_once "System.php";
    
    /**
     * Filesystem access using WebDAV
     *
     * @access public
     */
    class HTTP_WebDAV_Server_Filesystem extends HTTP_WebDAV_Server 
    {
        /**
         * Root directory for WebDAV access
         *
         * Defaults to webserver document root (set by ServeRequest)
         *
         * @access private
         * @var    string
         */
        var $base = "";
        var $dbaccess="user=anakeen dbname=freedom";
	var $racine=9;
        /** 
         * MySQL Host where property and locking information is stored
         *
         * @access private
         * @var    string
         */
        var $db_host = "localhost";

        /**
         * MySQL database for property/locking information storage
         *
         * @access private
         * @var    string
         */
        var $db_name = "webdav";

        /**
         * MySQL user for property/locking db access
         *
         * @access private
         * @var    string
         */
        var $db_user = "root";

        /**
         * MySQL password for property/locking db access
         *
         * @access private
         * @var    string
         */
        var $db_passwd = "";

        /**
         * Serve a webdav request
         *
         * @access public
         * @param  string  
         */
        function ServeRequest($base = false) 
        {
            // special treatment for litmus compliance test
            // reply on its identifier header
            // not needed for the test itself but eases debugging
            foreach(apache_request_headers() as $key => $value) {
                if (stristr($key,"litmus")) {
                    error_log("Litmus test $value");
                    header("X-Litmus-reply: ".$value);
                }
            }

            // set root directory, defaults to webserver document root if not set
            if ($base) { 
                $this->base = realpath($base); // TODO throw if not a directory
            } else if (!$this->base) {
                $this->base = $_SERVER['DOCUMENT_ROOT'];
            }
                
            // establish connection to property/locking db
            mysql_connect($this->db_host, $this->db_user, $this->db_passwd) or die(mysql_error());
            mysql_select_db($this->db_name) or die(mysql_error());
            // TODO throw on connection problems

            // let the base class do all the work
            parent::ServeRequest();
        }

        /**
         * No authentication is needed here
         *
         * @access private
         * @param  string  HTTP Authentication type (Basic, Digest, ...)
         * @param  string  Username
         * @param  string  Password
         * @return bool    true on successful authentication
         */
        function check_auth($type, $user, $pass) 
        {
            return true;
        }


        /**
         * PROPFIND method handler
         *
         * @param  array  general parameter passing array
         * @param  array  return array for file properties
         * @return bool   true on success
         */
        function PROPFIND(&$options, &$files) 
        {
            // get absolute fs path to requested resource
            $fspath =  $options["path"];
            
            error_log ( "===========>PROPFIND :".$options["path"] );

           
            // prepare property array
            $files["files"] = array();

            // store information for the requested path itself


            // information for contained resources requested?
            if (!empty($options["depth"]))  { // TODO check for is_dir() first?
                
                // make sure path ends with '/'
                $options["path"] = $this->_slashify($options["path"]);

                // try to open directory
                $freefiles=$this->readfolder($fspath);
                $files["files"]=$freefiles;
            } else {
                
                $freefiles=$this->readfolder($fspath,true);
                $files["files"]=$freefiles;
            }

            // ok, all done
            return true;
        } 
        

        function readfolder($fspath,$onlyfld=false) {
            include_once("FDL/Lib.Dir.php");
            global $action;

            $files=array();
            $action->user=new stdClass();
            $action->user->id=1;
            $fldid=$this->path2id($fspath);
           
            $fld=new_doc($this->dbaccess,$fldid);
	    error_log("READFOLDER FIRST:".dirname($fspath)."/".$fld->title."ONLY:".intval($onlyfld));
            //$files=$this->docpropinfo($fld,$this->_slashify(dirname($fspath)),true);
            $files=$this->docpropinfo($fld,$this->_slashify(($fspath)),true);

            if (! $onlyfld) {
	      /*
	      		$ldoc = getChildDoc($this->dbaccess, $fld->initid,0,"ALL", array(),$action->user->id,"ITEM");
		error_log("READFOLDER:".countDocs($ldoc));
		while ($doc=getNextDoc($this->dbaccess,$ldoc)) {
		  //		  $files[]=$this->docpropinfo($doc);
		error_log("READFOLDER examine :".$doc->title);
		$files=array_merge($files,$this->docpropinfo($doc,$fspath));
                }*/
	      $tdoc=getFldDoc($this->dbaccess, $fld->initid,array(),-1);
	      error_log("READFOLDER examine :".count($tdoc));
	      foreach ($tdoc as $k=>$v) {
		$doc=getDocObject($this->dbaccess,$v);
		$files=array_merge($files,$this->docpropinfo($doc,$fspath,false));
		
		
	      }

	      
            }
            return $files;
        }


        function path2id($fspath) {
	  //error_log("FSPATH :".$fspath);
	  if ($fspath=='/')     return $this->racine;

	  $fspath=$this->_unslashify($fspath);

	  $query = "SELECT  value FROM properties WHERE name='fid' and path = '".mysql_escape_string($fspath)."'";
	  //error_log("PATH2ID:".$query);
       
	  $res = mysql_query($query);
	  while ($row = mysql_fetch_assoc($res)) {
	    $fid= $row["value"];
	  }
	  mysql_free_result($res);
	  error_log("FSPATH2 :".$fspath. "=>".$fid);
	  return $fid;
        } 

        function docpropinfo(&$doc,$path,$firstlevel) 
        {
	  // map URI path to filesystem path
	  $fspath = $this->base . $path;

	  // create result array
	  $tinfo = array();
	  $info = array();
	  // TODO remove slash append code when base clase is able to do it itself
	  //$info["path"]  = is_dir($fspath) ? $this->_slashify($path) : $path; 
	  if ($doc->id == $this->racine) $doc->title= '';
            
	  // no special beautified displayname here ...
            
            
	  // creation and modification time

	  // type and size (caller already made sure that path exists)
	  if (($doc->doctype=='D')||($doc->doctype=='S')) {
	    // directory (WebDAV collection)	
	      $info = array();   
	    $info["props"] = array();
	    $info["props"][] = $this->mkprop("resourcetype", "collection");
	    $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");             
	    $info["props"][] = $this->mkprop("displayname", "/".utf8_encode($doc->title));
	    $path=$this->_slashify($path);
	    if ($firstlevel) $info["path"]  = $path;
	    else $info["path"]  = $path.utf8_encode($doc->title);
	    //$info["path"]  = $path;
	    $info["props"][] = $this->mkprop("creationdate",   $doc->revdate );
	    $info["props"][] = $this->mkprop("getlastmodified", $doc->revdate);
	    error_log("FOLDER:".$path.":".$doc->title);
            // get additional properties from database
            $query = "SELECT ns, name, value FROM properties WHERE path = '$path'";
            $res = mysql_query($query);
            while ($row = mysql_fetch_assoc($res)) {
	      $info["props"][] = $this->mkprop($row["ns"], $row["name"], $row["value"]);
            }
            mysql_free_result($res);
	    $tinfo[]=$info;
	    $query = "REPLACE INTO properties SET path = '".mysql_escape_string($info["path"])."', name = 'fid', ns= '$prop[ns]', value = '".$doc->initid."'";
	    mysql_query($query);	    
	  } else {
	    // simple document : search attached files     
  
	    // $info["props"][] = $this->mkprop("getcontenttype", $this->_mimetype($fspath));
	    $afiles=$doc->GetFilesProperties();
	    error_log("READFILES examine :".count($afiles).'-'.$doc->title.'-'.$doc->id);
	    $bpath=basename($path);
	    $dpath=$this->_slashify(dirname($path));
	    
	    error_log("FILEDEBUG:".$path."-".$bpath."-".$path);
	    

	    $path=$this->_slashify($path);
	    foreach ($afiles as $afile) {
	      $info = array();   
	      $info["props"][] = $this->mkprop("resourcetype", "");
	      if ((!$firstlevel ) || ($afile["name"] == $bpath)) {
		$info["props"][] = $this->mkprop("displayname", "/".utf8_encode($afile["name"]));
		if ($firstlevel) $info["path"]  = $dpath.utf8_encode($afile["name"]);
		else $info["path"]  = $path.utf8_encode($afile["name"]);
		$filename=$afile["path"];
		$info["props"][] = $this->mkprop("creationdate",   filectime($filename)) ;
		$info["props"][] = $this->mkprop("getlastmodified", filemtime($filename));
		$info["props"][] = $this->mkprop("getcontenttype", $this->_mimetype($filename));
		$info["props"][] = $this->mkprop("getcontentlength",intval($afile["size"] ));
		// get additional properties from database
		$query = "SELECT ns, name, value FROM properties WHERE path = '$path'";
		$res = mysql_query($query);
		while ($row = mysql_fetch_assoc($res)) {
		  $info["props"][] = $this->mkprop($row["ns"], $row["name"], $row["value"]);
		}
		mysql_free_result($res);
		$tinfo[]=$info;
		$query = "REPLACE INTO properties SET path = '".mysql_escape_string($info["path"])."', name = 'fid', ns= '$prop[ns]', value = '".$doc->id."'";
	       
		mysql_query($query);
		error_log("FILE:".$afile["name"]."-".$afile["size"]."-".$path);
	      }
	      //error_log("PROP:".$query);
	    }
	  }

	  return $tinfo;
        }
      
        

        /**
         * detect if a given program is found in the search PATH
         *
         * helper function used by _mimetype() to detect if the 
         * external 'file' utility is available
         *
         * @param  string  program name
         * @param  string  optional search path, defaults to $PATH
         * @return bool    true if executable program found in path
         */
        function _can_execute($name, $path = false) 
        {
            // path defaults to PATH from environment if not set
            if ($path === false) {
                $path = getenv("PATH");
            }
            
            // check method depends on operating system
            if (!strncmp(PHP_OS, "WIN", 3)) {
                // on Windows an appropriate COM or EXE file needs to exist
                $exts = array(".exe", ".com");
                $check_fn = "file_exists";
            } else { 
                // anywhere else we look for an executable file of that name
                $exts = array("");
                $check_fn = "is_executable";
            }
            
            // now check the directories in the path for the program
            foreach (explode(PATH_SEPARATOR, $path) as $dir) {
                // skip invalid path entries
                if (!file_exists($dir)) continue;
                if (!is_dir($dir)) continue;

                // and now look for the file
                foreach ($exts as $ext) {
                    if ($check_fn("$dir/$name".$ext)) return true;
                }
            }

            return false;
        }

        
        /**
         * try to detect the mime type of a file
         *
         * @param  string  file path
         * @return string  guessed mime type
         */
        function _mimetype($fspath) 
        {
	  return trim(`file -ib $fspath`);
            if (@is_dir($fspath)) {
                // directories are easy
                return "httpd/unix-directory"; 
            } else if (function_exists("mime_content_type")) {
                // use mime magic extension if available
                $mime_type = mime_content_type($fspath);
            } else if ($this->_can_execute("file")) {
                // it looks like we have a 'file' command, 
                // lets see it it does have mime support
                $fp = popen("file -i '$fspath' 2>/dev/null", "r");
                $reply = fgets($fp);
                pclose($fp);
                
                // popen will not return an error if the binary was not found
                // and find may not have mime support using "-i"
                // so we test the format of the returned string 
                
                // the reply begins with the requested filename
                if (!strncmp($reply, "$fspath: ", strlen($fspath)+2)) {                     
                    $reply = substr($reply, strlen($fspath)+2);
                    // followed by the mime type (maybe including options)
                    if (preg_match('/^[[:alnum:]_-]+/[[:alnum:]_-]+;?.*/', $reply, $matches)) {
                        $mime_type = $matches[0];
                    }
                }
            } 
            
            if (empty($mime_type)) {
                // Fallback solution: try to guess the type by the file extension
                // TODO: add more ...
                // TODO: it has been suggested to delegate mimetype detection 
                //       to apache but this has at least three issues:
                //       - works only with apache
                //       - needs file to be within the document tree
                //       - requires apache mod_magic 
                // TODO: can we use the registry for this on Windows?
                //       OTOH if the server is Windos the clients are likely to 
                //       be Windows, too, and tend do ignore the Content-Type
                //       anyway (overriding it with information taken from
                //       the registry)
                // TODO: have a seperate PEAR class for mimetype detection?
                switch (strtolower(strrchr(basename($fspath), "."))) {
                case ".html":
                    $mime_type = "text/html";
                    break;
                case ".gif":
                    $mime_type = "image/gif";
                    break;
                case ".jpg":
                    $mime_type = "image/jpeg";
                    break;
                default: 
                    $mime_type = "application/octet-stream";
                    break;
                }
            }
            
            return $mime_type;
        }

        /**
         * GET method handler
         * 
         * @param  array  parameter passing array
         * @return bool   true on success
         */
        function GET(&$options) 
        {
	    error_log("========>GET :".$options["path"]);
            include_once("FDL/Class.Doc.php");
            // get absolute fs path to requested resource
            $fspath = $this->base . $options["path"];

            $fldid=$this->path2id($options["path"]);
            $doc=new_doc($this->dbaccess,$fldid);
	    $afiles=$doc->GetFilesProperties();  
	    $bpath=basename($options["path"]);
	    error_log("GET SEARCH #FILES:".count($afiles));
	    foreach ($afiles as $afile) {
	      $path=utf8_encode($afile["name"]);
		error_log("GET SEARCH:".$bpath.'->'.$path);
	      if ($path == $bpath) {
		error_log("GET FOUND:".$path.'-'.$afile["path"]);
		$fspath=$afile["path"];
		
	      }
	    }
            // sanity check
            if (!file_exists($fspath)) return false;
            
            // is this a collection?
            if (is_dir($fspath)) {
                return $this->GetDir($fspath, $options);
            }
            
            // detect resource type
            $options['mimetype'] = $this->_mimetype($fspath); 
                
            // detect modification time
            // see rfc2518, section 13.7
            // some clients seem to treat this as a reverse rule
            // requiering a Last-Modified header if the getlastmodified header was set
            $options['mtime'] = filemtime($fspath);
            
            // detect resource size
            $options['size'] = filesize($fspath);
            
            // no need to check result here, it is handled by the base class
            $options['stream'] = fopen($fspath, "r");
            
            return true;
        }

        /**
         * GET method handler for directories
         *
         * This is a very simple mod_index lookalike.
         * See RFC 2518, Section 8.4 on GET/HEAD for collections
         *
         * @param  string  directory path
         * @return void    function has to handle HTTP response itself
         */
        function GetDir($fspath, &$options) 
        {
            $path = $this->_slashify($options["path"]);
            if ($path != $options["path"]) {
                header("Location: ".$this->base_uri.$path);
                exit;
            }

            // fixed width directory column format
            $format = "%15s  %-19s  %-s\n";

            $handle = @opendir($fspath);
            if (!$handle) {
                return false;
            }

            echo "<html><head><title>Index of ".htmlspecialchars($options['path'])."</title></head>\n";
            
            echo "<h1>Index of ".htmlspecialchars($options['path'])."</h1>\n";
            
            echo "<pre>";
            printf($format, "Size", "Last modified", "Filename");
            echo "<hr>";

            while ($filename = readdir($handle)) {
                if ($filename != "." && $filename != "..") {
                    $fullpath = $fspath."/".$filename;
                    $name = htmlspecialchars($filename);
                    printf($format, 
                           number_format(filesize($fullpath)),
                           strftime("%Y-%m-%d %H:%M:%S", filemtime($fullpath)), 
                           "<a href='$name'>$name</a>");
                }
            }

            echo "</pre>";

            closedir($handle);

            echo "</html>\n";

            exit;
        }

        /**
         * PUT method handler
         * 
         * @param  array  parameter passing array
         * @return bool   true on success
         */
        function PUT(&$options)  {
	  error_log("========>PUT :".$options["path"]);
	  include_once("FDL/Class.Doc.php");
	  $fspath = false;
	  $fldid=$this->path2id($options["path"]);

	  $doc=new_doc($this->dbaccess,$fldid);
	  $afiles=$doc->GetFilesProperties();  
	  $bpath=basename($options["path"]);
	  error_log("PUT SEARCH #FILES:".count($afiles));
	  foreach ($afiles as $afile) {
	    $path=utf8_encode($afile["name"]);
	    error_log("PUT SEARCH:".$bpath.'->'.$path);
	    if ($path == $bpath) {
	      error_log("PUT FOUND:".$path.'-'.$afile["path"]);
	      $fspath=$afile["path"];
		
	    }
	  }

	  $options["new"] = ! file_exists($fspath);
	    
	  if ($options["new"]) {
	    $dir=dirname($options["path"]);
	    error_log("PUT NEW FILE IN:".$dir);
	    $ndoc=createDoc($this->dbaccess,"FILE");
	    if ($ndoc) {
	      $fa=$ndoc->GetFirstFileAttributes();
	      $ndoc->SetTextValueInFile($fa->id, "--" ,basename($options["path"]));
	      $err=$ndoc->Add();
	      error_log("PUT NEW FILE:".$ndoc->id);
	      if ($err=="") {
		$afiles=$ndoc->GetFilesProperties();  
		$bpath=basename($options["path"]);
		error_log("PUT SEARCH2 #FILES:".count($afiles));
		foreach ($afiles as $afile) {
		  $path=utf8_encode($afile["name"]);
		  error_log("PUT SEARCH2:".$bpath.'->'.$path);
		  if ($path == $bpath) {
		    error_log("PUT FOUND2:".$path.'-'.$afile["path"]);
		    $fspath=$afile["path"];
		  }		
		}
	      }
	    }

	  }
	    

	  $fp = fopen($fspath, "w");

	  return $fp;
        }


        /**
         * MKCOL method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function MKCOL($options) 
        {           
            $path = $this->base .$options["path"];
            error_log ( "===========>MKCOL :".$options["path"] );
            $parent = dirname($path);
            $name = basename($path);

            if (!file_exists($parent)) {
                return "409 Conflict";
            }

            if (!is_dir($parent)) {
                return "403 Forbidden";
            }

            if ( file_exists($parent."/".$name) ) {
                return "405 Method not allowed";
            }

            if (!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
                return "415 Unsupported media type";
            }
            
            $stat = mkdir ($parent."/".$name,0777);
            if (!$stat) {
                return "403 Forbidden";                 
            }

            return ("201 Created");
        }
        
        
        /**
         * DELETE method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function DELETE($options) 
        {
            error_log ( "===========>DELETE :".$options["path"] );
            $path = $this->base . "/" .$options["path"];

            if (!file_exists($path)) {
                return "404 Not found";
            }

            if (is_dir($path)) {
                $query = "DELETE FROM properties WHERE path LIKE '".$this->_slashify($options["path"])."%'";
                mysql_query($query);
                System::rm("-rf $path");
            } else {
                unlink ($path);
            }
            $query = "DELETE FROM properties WHERE path = '$options[path]'";
            mysql_query($query);

            return "204 No Content";
        }


        /**
         * MOVE method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function MOVE($options) 
        {
            error_log ( "===========>MOVE :".$options["path"] );
            return $this->COPY($options, true);
        }

        /**
         * COPY method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function COPY($options, $del=false) 
        {
            error_log ( "===========>COPY :".$options["path"] );
            // TODO Property updates still broken (Litmus should detect this?)

            if (!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
                return "415 Unsupported media type";
            }

            // no copying to different WebDAV Servers yet
            if (isset($options["dest_url"])) {
                return "502 bad gateway";
            }

            $source = $this->base .$options["path"];
            if (!file_exists($source)) return "404 Not found";

            $dest = $this->base . $options["dest"];

            $new = !file_exists($dest);
            $existing_col = false;

            if (!$new) {
                if ($del && is_dir($dest)) {
                    if (!$options["overwrite"]) {
                        return "412 precondition failed";
                    }
                    $dest .= basename($source);
                    if (file_exists($dest)) {
                        $options["dest"] .= basename($source);
                    } else {
                        $new = true;
                        $existing_col = true;
                    }
                }
            }

            if (!$new) {
                if ($options["overwrite"]) {
                    $stat = $this->DELETE(array("path" => $options["dest"]));
                    if (($stat{0} != "2") && (substr($stat, 0, 3) != "404")) {
                        return $stat; 
                    }
                } else {                
                    return "412 precondition failed";
                }
            }

            if (is_dir($source) && ($options["depth"] != "infinity")) {
                // RFC 2518 Section 9.2, last paragraph
                return "400 Bad request";
            }

            if ($del) {
                if (!rename($source, $dest)) {
                    return "500 Internal server error";
                }
                $destpath = $this->_unslashify($options["dest"]);
                if (is_dir($source)) {
                    $query = "UPDATE properties 
                                 SET path = REPLACE(path, '".$options["path"]."', '".$destpath."') 
                               WHERE path LIKE '".$this->_slashify($options["path"])."%'";
                    mysql_query($query);
                }

                $query = "UPDATE properties 
                             SET path = '".$destpath."'
                           WHERE path = '".$options["path"]."'";
                mysql_query($query);
            } else {
                if (is_dir($source)) {
                    $files = System::find($source);
                    $files = array_reverse($files);
                } else {
                    $files = array($source);
                }

                if (!is_array($files) || empty($files)) {
                    return "500 Internal server error";
                }
                    
                
                foreach ($files as $file) {
                    if (is_dir($file)) {
                      $file = $this->_slashify($file);
                    }

                    $destfile = str_replace($source, $dest, $file);
                    
                    if (is_dir($file)) {
                        if (!is_dir($destfile)) {
                            // TODO "mkdir -p" here? (only natively supported by PHP 5) 
                            if (!mkdir($destfile)) {
                                return "409 Conflict";
                            }
                        } else {
                          error_log("existing dir '$destfile'");
                        }
                    } else {
                        if (!copy($file, $destfile)) {
                            return "409 Conflict";
                        }
                    }
                }

                $query = "INSERT INTO properties SELECT ... FROM properties WHERE path = '".$options['path']."'";
            }

            return ($new && !$existing_col) ? "201 Created" : "204 No Content";         
        }

        /**
         * PROPPATCH method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function PROPPATCH(&$options) 
        {
            global $prefs, $tab;
            error_log ( "===========>PROPPATCH :".$options["path"] );

            $msg = "";
            
            $path = $options["path"];
            
            $dir = dirname($path)."/";
            $base = basename($path);
            
            foreach($options["props"] as $key => $prop) {
                if ($prop["ns"] == "DAV:") {
                    $options["props"][$key]['status'] = "403 Forbidden";
                } else {
                    if (isset($prop["val"])) {
                        $query = "REPLACE INTO properties SET path = '$options[path]', name = '$prop[name]', ns= '$prop[ns]', value = '$prop[val]'";
                        error_log($query);
                    } else {
                        $query = "DELETE FROM properties WHERE path = '$options[path]' AND name = '$prop[name]' AND ns = '$prop[ns]'";
                    }       
                    mysql_query($query);
                }
            }
                        
            return "";
        }


        /**
         * LOCK method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function LOCK(&$options) 
        {
            error_log ( "===========>LOCK :".$options["path"] );
            if (isset($options["update"])) { // Lock Update
                $query = "UPDATE locks SET expires = ".(time()+300). "and token='".$options["update"]."'";
                mysql_query($query);
                
                if (mysql_affected_rows()) {
                    $options["timeout"] = 300; // 5min hardcoded
                    return true;
                } else {
                    return false;
                }
            }
            
            $options["timeout"] = time()+300; // 5min. hardcoded

            $query = "INSERT INTO locks
                        SET token   = '$options[locktoken]'
                          , path    = '$options[path]'
                          , owner   = '$options[owner]'
                          , expires = '$options[timeout]'
                          , exclusivelock  = " .($options['scope'] === "exclusive" ? "1" : "0")
                ;
            mysql_query($query);

            return mysql_affected_rows() ? "200 OK" : "409 Conflict";
        }

        /**
         * UNLOCK method handler
         *
         * @param  array  general parameter passing array
         * @return bool   true on success
         */
        function UNLOCK(&$options) 
        {
            error_log ( "===========>UNLOCK :".$options["path"] );
            $query = "DELETE FROM locks
                      WHERE path = '$options[path]'
                        AND token = '$options[token]'";
            mysql_query($query);

            return mysql_affected_rows() ? "204 No Content" : "409 Conflict";
        }

        /**
         * checkLock() helper
         *
         * @param  string resource path to check for locks
         * @return bool   true on success
         */
        function checkLock($path) 
        {
            $result = false;
            
            $query = "SELECT owner, token, expires, exclusivelock
                  FROM locks
                 WHERE path = '$path'
               ";
            $res = mysql_query($query);

            if ($res) {
                $row = mysql_fetch_array($res);
                mysql_free_result($res);

                if ($row) {
                    $result = array( "type"    => "write",
                                                     "scope"   => $row["exclusivelock"] ? "exclusive" : "shared",
                                                     "depth"   => 0,
                                                     "owner"   => $row['owner'],
                                                     "token"   => $row['token'],
                                                     "expires" => $row['expires']
                                                     );
                }
            }

            return $result;
        }


        /**
         * create database tables for property and lock storage
         *
         * @param  void
         * @return bool   true on success
         */
        function create_database() 
        {
            // TODO
            return false;
        }
    }


?>
