<?php

   require_once "google-api-php-client/src/Google_Client.php";
   require_once "google-api-php-client/src/contrib/Google_CalendarService.php";
   include 'retrieve.php';
   global $event_end_day;
   date_default_timezone_set('Asia/Calcutta');
   $tday=date('d');  //getting the time according to indian timezone.
   $tmonth=date('m');
   $tyear=date('Y');
   $today=$tyear."-".$tmonth."-".$tday."T00:01:00.000+05:30";
   fetch($today);   //this will prefetch the event already stored in our google calendar from today onwards.
  
  for($i=0;$i<$counter;$i++){
    $string_array[$i]=$_SESSION[$i];
    echo $string_array[$i]."<br/>";
    /*this loop put the different events from retrieve.php into $string_array variable
    this are the event which are already present in our google calendar.
    */
  } 
  if(is_null($string_array)){
    echo "string array is null";
  }
   session_destroy();  //destroying the session

   function numericmonth($month){
       if($month=='Jan'){
        return 1; 
      }else
       if($month=='Feb'){
        return 2; 
      }else
       if($month=='Mar'){
        return 3; 
      }else
       if($month=='Apr'){
        return 4; 
      }else
       if($month=='May'){
        return 5; 
      }else
       if($month=='Jun'){
        return 6; 
      }else
       if($month=='Jul'){
        return 7; 
      }else
       if($month=='Aug'){
        return 8; 
      }else
       if($month=='Sep'){
        return 9; 
      }else
       if($month=='Oct'){
        return 10; 
      }else
       if($month=='Nov'){
        return 11; 
      }else
       if($month=='Dec'){
        return 12; 
      }

   }

   /* add_event function add the event on google calendar using 
   google apis*/
   function add_event($e_name,$location,$event_detail,$es_year,$es_month,$es_day,$el_year,$el_month,$el_day,$es_hour,$es_minute,$el_hour,$el_minute){
   $client = new Google_Client();
   $client->setUseObjects(true);
   /* this is my sincere request to anyone who is seeing this code 
    * please don't mess with this google credential.
    * this calendar is for the welfare of everyone.
    * please don't try to misuse it.
    * 
   */
   $client->setApplicationName("HE-Cal");
   $client->setClientId("650696064454-fhrff2s8hd6kibn3cjn9ksjm3gleipir.apps.googleusercontent.compact(varname)");
   $client->setAssertionCredentials(new Google_AssertionCredentials(
           "650696064454-fhrff2s8hd6kibn3cjn9ksjm3gleipir@developer.gserviceaccount.com",
           array("https://www.googleapis.com/auth/calendar"),
           file_get_contents("certificates/58dc8debcb492676e29ba7c3d98befbf3ee251de-privatekey.p12")
       )
   );
   /*$starteventtime is the variable of starting time of event according to acceptable format for google calendar
   same is true for $endeventtime*/
   $starteventtime=$es_year."-".$es_month."-".$es_day."T".$es_hour.":".$es_minute.":00.000+05:30";
   $endeventtime=$el_year."-".$el_month."-".$el_day."T".$el_hour.":".$el_minute.":00.000+05:30";
   $service = new Google_CalendarService($client);

   $event = new Google_Event();
   $event->setSummary($e_name);//set event name.
   $event->setLocation($location);//set event location
   $event->setDescription($event_detail);//set event description.
   $start = new Google_EventDateTime();
   //old date and time format '2014-4-22T19:00:00.000+01:00'     '2014-4-25T19:25:00.000+01:00'
   $start->setDateTime($starteventtime);
   $start->setTimeZone('Asia/Kolkata');  //setting timezone
   $event->setStart($start);
   $end = new Google_EventDateTime();
   $end->setDateTime($endeventtime);
   $end->setTimeZone('Asia/Kolkata');
   $event->setEnd($end);
   
   $calendar_id = "gu8k601ev6u41q9udo7fc65hb0@group.calendar.google.com";  
   //calendar id specific to my calendar generated by google calendar.
   
   $new_event = null;
   
   try {
       $new_event = $service->events->insert($calendar_id, $event);
       $new_event_id= $new_event->getId();
   } catch (Google_ServiceException $e) {
       syslog(LOG_ERR, $e->getMessage());
       echo $e->getMessage();  //very helpful for debuging :D
   }
   
   $event = $service->events->get($calendar_id, $new_event->getId());
   
   if ($event != null) {
       echo "Inserted:";
       echo "EventID=".$event->getId();
       echo "Summary=".$event->getSummary();
       echo "Status=".$event->getStatus()."<br/><br/>";
   }
 } //end of addevent fun
   

   $url = 'http://www.hackerearth.com/chrome-extension/events/';  //fetching the Hackerearth event from this url.
   $JSON = file_get_contents($url);

    $data = json_decode($JSON);
   foreach ($data as $item) {
  
      $event_title=$item->title;//it store the title name of new event from hackerearth feeds.


      $whatToStrip = array("?","!",",",";"); // Add what you want to strip in this array
      str_replace($whatToStrip, " ", $event_title);
      $event_description=$item->description."<br/>".$item->url;
      $str=explode(" ",$item->time);

     // echo $item->end_time."<br>".$item->end_date."<br>";
      $end_hour_min=explode(" ",$item->end_time);
      $end_AM_PM =$end_hour_min[1];
      $end_hour=explode(":",$end_hour_min[0]);// it store end hour
      $end_minute=$end_hour[1];//it store end minutes of the event.
      $end_hour=$end_hour[0];
      $end_date_month_year=explode(" ",$item->end_date);
      $end_date=$end_date_month_year[0];
      $end_month=$end_date_month_year[1];
      $end_year=$end_date_month_year[2];

      $str2=explode(":", $str[0]);
      $date=explode(" ",$item->date);  //store the date of commencement of new event of hackerearth.
     
      
      $event_start_day=$date[1];
      $event_start_month=numericmonth($date[2]);
      $event_start_year=$date[3];
      $event_start_hour=$str2[0];
      $event_start_minute=$str2[1];
      $event_end_day=$end_date;
      $event_end_month=numericmonth($end_month);
      $event_end_year=$end_year;
      $event_end_minute=$end_minute;
      $event_end_hour=$end_hour;
     
      if($str[1]=='PM'){
        if($str2[0]!=12){
          $event_start_hour=$str2[0]+12;  //converting the hour into 24 hour system.
        }else{
          $event_start_hour=12;
        } 
      }
      if($str[1]=="AM"){
         if($start_hour==12){
          $event_start_hour=0;
        }
      }

      if($end_AM_PM=='PM'){
        if($end_hour!=12){
          $event_end_hour=($end_hour + 12);
        }
        else{
          $event_end_hour=12;
        }
      }
      if($end_AM_PM=='AM'){
        if($end_hour==12){
          $event_end_hour=0;
        }
      }
      if(in_array($event_title, $string_array)==false||is_null($string_array)==true){
        /* this if block will only add the event if that event is not present in our calendar.*/
          add_event($event_title,"Web",$event_description,$event_start_year,$event_start_month,$event_start_day,$event_end_year,$event_end_month,$event_end_day,$event_start_hour,$event_start_minute,$event_end_hour,$event_end_minute);
        /*event city can be any city I put New Delhi but yes Hackerearth events can be accessed from anywhere in world google calendar will adjust timing
          for you according to your timezone*/
      }
    

  }
    
  
 
?>