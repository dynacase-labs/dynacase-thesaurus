
<!-- HEAD HTML -->

<html>
  <head>

   <title>[WHAT] [APP_TITLE]</title>

   <style>
[ZONE CORE:GENCSS]
   </style>


[CSS:REF]


   <style type="text/css">
[CSS:CODE]
   </style>

[JS:REF]

   <script language="JavaScript">
  <!--
    [JS:CODE]
  //-->
   </script>   



 </head>

<body class="freedom">

                         


<!-- Title Table -->
<form  class="fborder" name="modifyfreedom" method="POST" ENCTYPE="multipart/form-data" action="[CORE_STANDURL]&app=[FREEDOM_APP]&action=FREEDOM_MOD&id=[id]" >

      <INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="[UPLOAD_MAX_FILE_SIZE]">
                         

<input type="hidden" name="title" value="[TITLE]"> 

<div class="TITLE">
<table width="100%"  cellspacing="0" cellpadding="0" >
<tr>
 <td class="FREEDOMTblTitle">
   <span class="FREEDOMTextBigTitle">[TITLE]</span>
  </td> 
</tr>
</table>
</div>
<!-- Frame Table -->


<table border="0" width="100%" border="0" cellspacing="0" cellpadding="0" >


<!-- TABLEBODY -->
[BLOCK TABLEBODY]


<tr>

  <td class="FREEDOMTblFrame" colspan="3" ><span class="FREEDOMTextTitle"> [frametext]</span></td>
</tr>



[BLOCK [TABLEVALUE]]

<tr>

  <td class = "tdstyle" width="10%">&nbsp;</td>
  <td class = "tdstyle"  width="30%"><span class="FREEDOMTextName">[name]&nbsp;:&nbsp;</span></td>
  <td class = "tdstyle" width="60%">[inputtype]</td>
</tr>

[ENDBLOCK [TABLEVALUE]]

[ENDBLOCK TABLEBODY]

<tr>
  <tr>
  <td colspan="3" class = "tdstyle">
 <input type="submit" value=[editaction] onclick="javascript:parent.list.location.reload(true);"> 
  </td>
  </tr>

</table>


</form>
<!--  FOOT HTML -->

</body>
</html>
