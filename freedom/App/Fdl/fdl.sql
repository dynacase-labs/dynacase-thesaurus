create or replace function in_textlist(text, text) 
returns bool as '
declare 
  arg_tl alias for $1;
  arg_v alias for $2;
  rvalue bool;
  wt text;
begin
  rvalue := (arg_tl = arg_v) ;
  if (not rvalue) then	
    
     -- search in middle
    wt := \'\n\'||arg_v||\'\r\';
    rvalue := (position(wt in arg_tl) > 0);

     -- search in begin
     if (not rvalue) then	
       wt := arg_v||\'\r\';
       rvalue := (position(wt in arg_tl) = 1);

	
        -- search in end
       if (not rvalue) then	
          wt := \'\n\'||arg_v;
          rvalue := (position(wt in arg_tl) = (char_length(arg_tl)-char_length(arg_v))) and (position(wt in arg_tl) > 0);	
        end if;
     end if;
  end if;
  return rvalue;
end;
' language 'plpgsql';

create or replace function hasviewprivilege(int, int) 
returns bool as '
declare 
  arg_user alias for $1;
  profid alias for $2;
  classid int;
  aclid int;
begin
   if (arg_user = 1) then 
     return true; -- it is admin user
   end if;

   if (profid = 0) then
	return true; -- no controlled object
   end if;

  

   select into classid application.id from application, doc where doc.id=profid and lower(doc.classname) = application.name;
   select into aclid id from acl where id_application=classid and name=''view'';

   return hasprivilege(arg_user, profid, classid ,aclid );
end;
' language 'plpgsql';


create or replace function getdocvalues(int) 
returns varchar as '
declare 
  arg_doc alias for $1;
  rvalue docvalue%ROWTYPE;
  values text;
begin
values := '''';
for rvalue in select  * from docvalue where  (docid=arg_doc)  loop
	values := values || ''['' || rvalue.attrid || '';;'' || rvalue.value || '']'';
end loop;
return values;
end;
' language 'plpgsql';


create or replace function getdocavalues(int) 
returns varchar as '
declare 
  arg_doc alias for $1;
  rvalue docvalue%ROWTYPE;
  values text;
begin
values := '''';
for rvalue in select  docvalue.* from docvalue, docattr where  (docvalue.docid=arg_doc) and docvalue.attrid=docattr.id and docattr.abstract=''Y'' loop
	values := values || ''['' || rvalue.attrid || '';;'' || rvalue.value || '']'';
end loop;
return values;
end;
' language 'plpgsql';
