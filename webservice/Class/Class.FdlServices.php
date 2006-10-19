<?php

Class FdlServices {
  function __construct($login) {

    global $action;
    $this->_initFreedom($login,$action);

    $this->action=&$action;
  }


  private function  _initFreedom($login,&$a) {
    
    include_once('WHAT/Class.User.php');
    include_once('WHAT/Class.Session.php');

    $CoreNull="";
    $core = new Application();
    $core->Set("CORE",$CoreNull);
    $core->session=new Session();
    $a=new Action();
    $a->Set("",$core);
    $a->user=new User(); //create user as admin  
    $a->user->setLoginName($login);
  }

  public function add ($x,$y) {
    return $x+$y;
  }
 

  /**
   * change password for a user
   * @param string $login the login of the user
   * @param string $password the new password in clear text
   * @return string error message if one else empty string if OK
   */
  public function changePassword($login,$password) {
    if (! $this->action->hasPermission("FUSERS_MASTER","FUSERS")) return sprintf(_("permission FUSERS_MASTER:FUSERS needed\n"));

    $u = new User();
    if ($u->SetLoginName($login)) {
      if ($u->fid > 0) {	
	include_once('../FDL/Class.Doc.php');
	$du=new_doc($this->action->getParam("FREEDOM_DB"),$u->fid);
	if ($du->isAlive()) {
	  $err=$du->control("edit");
	  if ($err != "") return $err;
	}
      }
      $u->password_new=$password;
      $err=$u->modify();
      if ($err != "") return $err;
    } else {
      return sprintf(_("login %s not found\n"),$login);
    }
  }
   /**
   * get list of avalaibel application for a user
   * @param string $login the login of the user
   * @return array list of application
   */
  public function getAvailableApplication($login) {
    $this->_initFreedom($login,$a);
    
    if ($a->user->SetLoginName($login)) {
      return $a->GetAvailableApplication();
    } else {
      return sprintf(_("login %s not found\n"),$login);
    }    
  }
}

?>