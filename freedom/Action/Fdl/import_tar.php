<?php
/**
 * Import Set of documents and files with directories
 *
 * @author Anakeen 2000 
 * @version $Id: import_tar.php,v 1.1 2004/03/16 14:12:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/import_file.php");

define("TARUPLOAD","/tmp/upload/");
define("TAREXTRACT","/extract/");
define("TARTARS","/tars/");


function getTarUploadDir(&$action) {
  return TARUPLOAD.$action->user->login.TARTARS;
}
function getTarExtractDir(&$action,$tar) {
  return TARUPLOAD.$action->user->login.TAREXTRACT.$tar."_D";
}


/**
 * import a directory files
 * @param action $action current action
 * @param string $ftar tar file
 */
function import_tar(&$action,$ftar,$dirid=0,$famid=7) {

  import_directory($action,"/tmp/z/",$dirid,$famid,false);
}

/**
 * import a directory files
 * @param action $action current action
 * @param string $ldir local directory path
 */
function import_directory(&$action, $ldir,$dirid=0,$famid=7,
			  $onlycsv=false,$analyze=false) {
  // first see if fdl.csv file
  global $importedFiles;
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (is_dir($ldir)) {
    if ($handle = opendir($ldir)) {
      $lfamid=0;
      while (false !== ($file = readdir($handle))) {
	$absfile="$ldir/$file";
	$absfile=str_replace("//","/","$ldir/$file");
     
	if (is_file($absfile) && ($file=="fdl.csv")) {
	  $tr = analyze_csv($absfile,$dbaccess,$dirid,$lfamid,$analyze);
	
	}
      }
      if ($lfamid > 0) $famid=$lfamid; // set local default family identificator

      rewinddir($handle);
   
      /* This is the correct way to loop over the directory. */
      $defaultdoc= createDoc($dbaccess,$famid);
      if (($lfamid == 0) && ($famid==7)) {
	$defaultimg= createDoc($dbaccess,"IMAGE");
	$fimgattr=$defaultimg->GetFirstFileAttributes();
      }
      $newdir= createDoc($dbaccess,"DIR");
      $ffileattr=$defaultdoc->GetFirstFileAttributes();
  
      if ($dirid > 0) {
	$dir = new Doc($dbaccess,$dirid);
      }

      $nfile=0;
      while (false !== ($file = readdir($handle))) {
	$nfile++;
	$absfile=str_replace("//","/","$ldir/$file");
	$level = substr_count( $absfile,"/");
	$index="f$level/$nfile";
	if (is_file($absfile)) {
	  if (!$onlycsv) { // add also unmarked files
	  
	    if (!isset($importedFiles[$absfile])) {
	      $tr[$index]=array("err"=>"",
				"folderid"=>0,
				"foldername"=>$ldir,
				"filename"=>$file,
				"title"=>"",
				"id"=>0,
				"familyid"=>$ddoc->fromid,
				"familyname"=>"",
				"action"=>"");
	      $err=AddVaultFile($dbaccess,$absfile,$analyze,$vfid);
      
	      if ($err != "") {
		$tr[$index]["err"]=$err;
	      } else {
		if (($lfamid == 0) && ($famid==7) && (substr($vfid,0,5)=="image")){
		  $ddoc=&$defaultimg;
		  $fattr=$fimgattr->id;
		} else {
		  $ddoc=&$defaultdoc;
		  $fattr=$ffileattr->id;
		}
		$tr[$index]["familyid"]=$ddoc->fromid;
		$tr[$index]["action"]=_("to be add");
		if (! $analyze) {
		  $ddoc->Init();
		  $ddoc->setValue($fattr,$vfid);
		  $err=$ddoc->Add();
		  if ($err!="") {
		    $tr[$index]["action"]=_("not added");
		  } else {
		    $tr[$index]["action"]=_("added");
		    $tr[$index]["id"]=$ddoc->id;
		    $ddoc->PostModify();
		    $ddoc->Modify();
		    if ($dirid > 0) {
		      $dir->AddFile($ddoc->id);
		    }
		  }
		}
	      }
	    }
	  }
	} else if (is_dir($absfile) && ($file[0]!='.')) {
	  $tr[$index]=array("err"=>"",
			    "folderid"=>0,
			    "foldername"=>$ldir,
			    "filename"=>$file,
			    "title"=>"",
			    "id"=>0,
			    "familyid"=>$newdir->fromid,
			    "familyname"=>"",
			    "action"=>_("to be add"));
	  if (! $analyze) {
	    $newdir->Init();
	    $newdir->setValue("ba_title",$file);
	    $err=$newdir->Add();
	    if ($err!="") {
	      $tr[$index]["action"]=_("not added");
	    } else {
	      $tr[$index]["action"]=_("added");
	      if ($dirid > 0) {
		$dir->AddFile($newdir->id);	 
	      }
	    }
	  }
	  $itr=import_directory($action, $absfile,$newdir->id,$famid,$onlycsv,$analyze);
	  $tr=array_merge($tr,$itr);
	}
      }

   

      closedir($handle);
      return $tr;
  
    } 
  }  else {
    $err = sprintf("cannot open local directory %s",$ldir);
    return array("err"=>$err);
  }
}

function analyze_csv($fdlcsv,$dbaccess,$dirid,&$famid,$analyze) {
 
  $tr=array();
  $fcsv=fopen($fdlcsv,"r");
  if ($fcsv) {
    $ldir=dirname($fdlcsv);
    while ($data = fgetcsv ($fcsv, 2000, ";")) {
      $nline++;
      $level = substr_count( $ldir,"/");
      $index="c$level/$nline";
      switch ($data[0]) {
	// -----------------------------------
      case "DFAMID":
	$famid =  $data[1];
	print "\n\n change famid to $famid\n";
	break; 
      case "DOC":
	$tr[$index]=csvAddDoc($dbaccess, $data, $dirid,$analyze,$ldir);
	if ($tr[$index]["err"]=="") $nbdoc++;

	 
	break;    
      }
    }
    fclose($fcsv);
  }
  return $tr;
}
/**
 * decode characters wihich comes from windows zip
 * @param $s string to decode
 * @return string decoded string
 */
function WNGBdecode($s) {
  $td=array(144=>"É",
	    130=>"é",
	    133=>"à",
	    135=>"ç",
	    138=>"è",
	    151=>"ù",
	    212=>"È",
	    210=>"Ê",
	    128=>"Ç",
	    183=>"ê",
	    136=>"û",
	    183=>"À",
	    136=>"ê",
	    150=>"û",
	    147=>"ô",
	    137=>"ë",
	    139=>"ï");

  $s2=$s;
  for ($i=0;$i<strlen($s);$i++) {
    if (isset($td[ord($s[$i])]))  $s2[$i]=$td[ord($s[$i])];
      
  }
  return $s2;
}

/**
 * rename file name which comes from windows zip
 * @param $ldir directory to decode
 * @return void
 */
function WNGBDirRename($ldir) {
  $handle=opendir($ldir);
  while (false !== ($file = readdir($handle))) {
   if ($file[0] != ".") {
     $afile="$ldir/$file";

     if (is_file($afile)) {
       rename($afile,"$ldir/".WNGBdecode($file));
     } else if (is_dir($afile)) {
       WNGBDirRename($afile);
     }
   }
 }
 
 closedir($handle);
 rename($ldir,WNGBdecode($ldir)); 
}
?>
