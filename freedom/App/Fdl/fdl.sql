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
