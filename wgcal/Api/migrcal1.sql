SELECT '$'as begin,webcal_entry.cal_id,wid,cal_lastname||' '||cal_firstname as name,
     webcal_entry.cal_name,
     cal_description,
     cal_remind,cal_data,
     webcal_entry.cal_date,webcal_entry.cal_time,webcal_entry.cal_duration,
     cal_access
from webcal_entry, webcal_user, webcal_site_extras
     
      
where webcal_entry.cal_create_by=webcal_user.cal_login 
and webcal_entry.cal_id=webcal_site_extras.cal_id
and wid > 0;
