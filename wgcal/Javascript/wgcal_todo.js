var todoInEdition = { id:-1, title:'', date:'', note:'' };

function todoLoad(event, id) {
  todoInEdition = { id:-1, title:'', date:'', note:'' };;
  var dv = fcalGetJSDoc(event, id);
  if (!dv) return false;
  todoInEdition.id = id;
  todoInEdition.title = dv.todo_title;
  todoInEdition.date = dv.todo_date;
  todoInEdition.note = dv.todo_note;
  return true;
}

function todoEdit(event, id) {
  globalcursor('progress');
 
  if (id>0)  {
    todoLoad(event, id);
    eltId('todoTitle').value = todoInEdition.title;
    var sd = new String(todoInEdition.date);
    var ttime = new Date( sd.substr(6,4),
                          parseInt(sd.substr(3,2),10)-1,
                          sd.substr(0,2),
                          12, 0, 0, 0);
    eltId('todoStart').value = ttime.getTime() / 1000;
    eltId('todoTextDate').innerHTML = ttime.print('%a %d %b %Y');;
    eltId('todoNote').value = todoInEdition.note;
    eltId('btnSave').style.visibility = 'visible';   
  } else {
    var ctime = new Date();
    var ttime = new Date(ctime.getFullYear(), 
			 ctime.getMonth(), 
			 (ctime.getDate()+7), 12, 0, 0, 0);
    
    eltId('todoTitle').value = '';
    eltId('todoStart').value = ttime.getTime() / 1000;
    eltId('todoTextDate').innerHTML = ttime.print('%a %d %b %Y');;
    eltId('todoNote').value = '';
    eltId('btnSave').style.visibility = 'hidden';
  }
  unglobalcursor();
  todoShowEdit();
}

var todoChanged = false;

function todoTitleChange(event) {
  var evt = (evt) ? evt : ((event) ? event : null );
  var cc = (evt.keyCode) ? evt.keyCode : evt.charCode;
  if (cc==13) {
    todoSave(event);
    return false;
  }
  if (eltId('todoTitle').value!='') {
    todoChanged = true;
    eltId('btnSave').style.visibility = 'visible';
  }  else {
    eltId('btnSave').style.visibility = 'hidden';
    todoChanged = false;
  }
  return false;  
}

function todoSave(event) {
  globalcursor('progress');
  var tTitle = eltId('todoTitle').value;
  var tStop = eltId('todoStart').value;
  var tNote = eltId('todoNote').value;

  var urlsend = "index.php?sole=Y&app=WGCAL&action=WGCAL_TODOSTORE";
  urlsend += "&id="+todoInEdition.id;
  urlsend += "&title="+escape(tTitle);
  urlsend += "&end="+escape(tStop);
  urlsend += "&note="+escape(tNote);
  
  var rq;
  if (window.XMLHttpRequest) rq = new XMLHttpRequest();
  else rq = new ActiveXObject("Microsoft.XMLHTTP");
  rq.open("POST", urlsend, false);
  rq.send('');
  
  todoChanged = false;
  unglobalcursor();
  document.location.reload(false);
}

function todoCancelEdit() {
  todoHideEdit();
  todoChanged = false;
}

function todoShowEdit() {
  eltId('todoeditzone').style.display = 'block';
  eltId('todolistzone').style.display = 'none';
  eltId('addTodo').style.visibility = 'hidden';
}
function todoHideEdit() {
  eltId('todoeditzone').style.display = 'none';
  eltId('todolistzone').style.display = 'block';
  eltId('addTodo').style.visibility = 'visible';
}


