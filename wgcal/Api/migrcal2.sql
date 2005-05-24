SELECT webcal_entry_user.*, wid,cal_lastname||' '||cal_firstname as name
from webcal_entry_user, webcal_user
     
      
where webcal_entry_user.cal_login=webcal_user.cal_login
and cal_status != 'D'
and wid > 0;