// --------------------------------------------------------
function Fade(elt, size) {
  elt.width += size;  
  elt.height += size;
}
function UnFade(id, size) {
  document.getElementById(id).width -= size;  
  document.getElementById(id).height -= size;
}
