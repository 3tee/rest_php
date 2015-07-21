<html>
  <body>
      <h1>PHP Rest demo </h1>

		<?php
		    include 'rest.php';
			
			$restServer = "192.168.2.2:9080";
			$accessKey = "demo_access";
            $secretKey = "demo_secret";
			
			try{
			   $rest = new Rest($restServer);
			   $accessTocken = $rest->getAccessTocken($accessKey,$secretKey);
			   echo "accessTocken = $accessTocken<br><br>";
			}catch(AuthException $e){
			   //echo "$e";
			   $errorMessage = $e->errorMessage();
			   echo "AuthException:errorMessage.code =$errorMessage->code\n";
			   echo "errorMessage.message =$errorMessage->message<br><br>";
			}
			
			$roomType = 1;//临时会议 ：1  缺省值  ;永久会议:  2
			$topic = "toptest";
			$maxAttendee= 50;
			$maxVideo = 5;
			$maxAudio = 5;
			$hostPassword = "20";
			$startTime = time(); //要求GMT时间 ,也可以设置为null
			$endTime = time()+60*60*3; //要求GMT时间,也可以设置为null,界面上显示参考：date_default_timezone_set('Etc/GMT-8');  date("Y-m-d H:i:s",time()+60*60*3)
			
			try{
				$roomId = $rest->createRoom($accessTocken,$topic,$maxAttendee,$maxVideo, $maxAudio,$hostPassword,$startTime,$endTime,$roomType);
				echo "roomId = $roomId<br><br>";
			   
			    $room = $rest->getRoom($roomId);
				echo "room.roomId = $room->roomId<br><br>";
				echo "room.roomURL = $room->roomURL<br><br>";
				
				$topic1 = "toptest111111";
			    $maxAttendee1= 30;
			    $maxVideo1 = 10;
			    $maxAudio1 = 20;
				$updateRoomRet = $rest->updateRoom($roomId,$accessTocken,$topic1,$maxAttendee1,$maxVideo1, $maxAudio1,null,null,null,null);
				echo "updateRoom, ret = $updateRoomRet<br><br>";
				
				$ret = $rest->delRoom($accessTocken,$roomId);
				echo "delRoom, ret = $ret<br><br>";
				
			}catch(RoomException $e){
			    //echo "$e";
			    $errorMessage = $e->errorMessage();
			    echo "RoomException:errorMessage.code =$errorMessage->code\n,";
			    echo "errorMessage.message =$errorMessage->message<br><br>";
			}
			
?>
		
  </body>
</html>