/* 
FCKCommands.RegisterCommand(commandName, command)
       commandName - Command name, referenced by the Toolbar, etc...
       command - Command object (must provide an Execute() function).
*/
var oQuickSave = new Object() ;
oQuickSave.Name = 'QuickSave' ;

// This is the standard function used to execute the command (called when clicking in the context menu item).
oQuickSave.Execute = function()
{
  window.parent.quicksave();
}
// This is the standard function used to retrieve the command state (it could be disabled for some reason).
oQuickSave.GetState = function()
{
	// Let's make it always enabled.
	return FCK_TRISTATE_OFF ;
}


// Register the related commands.
FCKCommands.RegisterCommand('QuickSave',oQuickSave );

// Create the "Find" toolbar button.
var oQuickSaveItem = new FCKToolbarButton('QuickSave', FCKLang['DlgQuickSaveTitle']);
oQuickSaveItem.IconPath = FCKConfig.PluginsPath + 'quicksave/floppy.png' ;

// 'QuickSave' is the name used in the Toolbar config.
FCKToolbarItems.RegisterItem( 'QuickSave', oQuickSaveItem ) ;

