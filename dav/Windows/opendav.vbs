Dim oShell
Dim Cmd
Dim OpenAs
Dim i
Dim Thepath
Dim Tab


'MsgBox(Wscript.Arguments(1))
if (Wscript.Arguments(0) = 1) then
  OpenAs = 1
else
  OpenAs = 0
end if

Set oShell = CreateObject("WScript.Shell")

i=InStr(Wscript.Arguments(1),":")
Thepath=Mid(Wscript.Arguments(1),i+1)


if (OpenAs) then
  'Cmd = "rundll32.exe c:\WINDOWS\system32\shell32.dll,OpenAs_RunDLL " & Wscript.Arguments(1)

  Cmd = "rundll32.exe c:\WINDOWS\system32\shell32.dll,OpenAs_RunDLL http:" & Thepath
else
  Cmd = """" & replace(Thepath,"/","\") & """"
  Tab=split(Thepath,"/")
 'wscript.echo ubound(tab)

 ' for i=0 to ubound(Tab)
 '   if (i>3) then
 '     Tab(i)= """" & Tab(i) & """"
 '   end if
 ' next
 ' Cmd = join(Tab,"\")
end if
MsgBox("["&Cmd&"]")
oShell.Run Cmd