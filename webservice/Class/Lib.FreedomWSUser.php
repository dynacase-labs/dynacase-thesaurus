<?php
/**
   * get list of avalaibel application for a user
   * @param string $login the login of the user
   * @return array list of application
   */
   function getAvailableApplication($login) {
  //print "<H1>COUCOU</H1>";
    global $action;
    _initFreedom();



    return array("coucou"=>"test");
    if ($action->user->SetLoginName($login)) {
      
      return $action->GetAvailableApplication();
    } else {
      return sprintf(_("login %s not found\n"),$login);
    }    
  }
?>