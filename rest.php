<?php

  final class  Rest{
         private $restServer;
         private $webServer;
		 private $accessKey;
		 private $secretKey;
		 private $signature;
		 private $roomMode = 1;
		 

         public function __construct($restServer) {
             $this->restServer = $restServer;
             $this->webServer = $this->getWebServer();
         }
		 
		 public function  getAccessTocken($accessKey,$secretKey) {
        	    $this->accessKey = $accessKey;
			$this->secretKey = $secretKey;
     	    $this->signature = $this->getSignature($this->accessKey,$this->secretKey);
			return $this->auth();
	     }
		 
		 public function createRoom($accessTocken,$topic,$maxAttendee,$maxVideo, $maxAudio,$hostPassword,$startTime,$endTime,$roomType){
		 	 	
			 	  $urlStr  = "http://" .$this->restServer."/rtc/room/create?owner_id=1&access_tocken=".$accessTocken
			 	              ."&room_mode=".$this->roomMode
			 	              ."&topic=".urlencode($topic)
			 	              ."&max_video=".$maxVideo
			 	              ."&max_audio=".$maxAudio
			 	              ."&attendee_max=".$maxAttendee
			 	              ."&host_password=".$hostPassword;
				  
				  if(!is_null($startTime)  &&  $startTime != "" ){
					   $urlStr= $urlStr."&start_time=".$startTime;
				  }	
				  
				  if(!is_null($endTime)  &&  $endTime != "" ){
					   $urlStr= $urlStr."&end_time=".$endTime;
				  }	
				  
				  if(is_null($roomType)  ||  $roomType == '' ){
				  	   $roomType=1;
				  }	  
			 	  $urlStr= $urlStr."&room_type=".$roomType; 
				  
				  $re = $this->sockGet($urlStr);
	              if(!is_null($re)  && $re != "" ){
		              $json = json_decode($re);
					  $ret = $json->{'ret'};
					  if(0 == $ret){
				    		  $roomId = $json->{'room_id'};
				    		  return $roomId;
					  }else{
					  	  $msg = $json->{'msg'};
						  throw new RoomException($msg,$ret);
					  }
				  }else{
				  	  throw new RoomException('un created',401);
				  }
		 }

         public function updateRoom($roomId,$accessTocken,$topic,$maxAttendee,$maxVideo, $maxAudio,$hostPassword,$startTime,$endTime,$roomType){
		 	
			 	  $urlStr  = "http://" .$this->restServer."/rtc/room/update?room_id=".$roomId."&access_tocken=".$accessTocken;
			 	           
				  if(!is_null($topic)  && $topic != '' ){
					   $urlStr= $urlStr."&topic=".$topic;
				  }	
				  
				  if(!is_null($maxVideo)  && $maxVideo != '' ){
					   $urlStr= $urlStr."&max_video=".$maxVideo;
				  }
				  
				  if(!is_null($maxAudio)  && $maxAudio != '' ){
					   $urlStr= $urlStr."&max_audio=".$maxAudio;
				  }
				  
				  if(!is_null($maxAttendee)  && $maxAttendee != '' ){
					   $urlStr= $urlStr."&attendee_max=".$maxAttendee;
				  }
				  
				  if(!is_null($hostPassword)  && $hostPassword != '' ){
					   $urlStr= $urlStr."&host_password=".$hostPassword;
				  }
				  
				  if(!is_null($startTime)  && $startTime != "" ){
					   $urlStr= $urlStr."&start_time=".$startTime;
				  }	
				  
				  if(!is_null($endTime)  && $endTime != "" ){
					   $urlStr= $urlStr."&end_time=".$endTime;
				  }	
				  
				  if(!is_null($roomType)  &&  $roomType !='' ){
					   $urlStr= $urlStr."&room_type=".$roomType;
				  }	
							  
				  $re = $this->sockGet($urlStr);
	              if(!is_null($re)  && $re != "" ){
		              $json = json_decode($re);
					  $ret = $json->{'ret'};
					  if(0 == $ret){
					  	  return $ret;
					  }else{
					  	  $msg = $json->{'msg'};
						  throw new RoomException($msg,$ret);
					  }
				  }else{
				  	  throw new RoomException('un created',401);
				  }
		  }
		 
		  public function delRoom($accessTocken,$roomId){
		 	 
			 	  $urlStr  = "http://" .$this->restServer."/rtc/room/del?access_tocken=".$accessTocken."&room_id=".$roomId;
				  $re = $this->sockGet($urlStr);
	              if(!is_null($re)  && $re != "" ){
		              $json = json_decode($re);
					  $ret = $json->{'ret'};
					  if(0 == $ret){
				    		  return $ret;
					  }else{
					  	  $msg = $json->{'msg'};
						  throw new RoomException($msg,$ret);
					  }
				  }else{
				  	  throw new RoomException('un del',401);
				  }
		 }
		 
		 
		 public function getRoom($roomId){
		 	$roomURL="http://".$this->webServer."/jroom/?roomId=".$roomId;
		 	$room  = new stdObject();
            $room->roomId = $roomId;
            $room->roomURL =$roomURL;
		 	return  $room;
		 }
		
			 
        /**
	    * TDODO 
	    * 服务器端返回WebServer,现暂时返回 restServer
	    */
		private function getWebServer(){
			return $this->restServer;
		}
		
		private function getSignature($str, $key){
			$utf8Str = mb_convert_encoding($str, "UTF-8");
			$hash_hmac = hash_hmac("sha1", $utf8Str, $key);
			$hmac_sha1_base64 = base64_encode($hash_hmac);
			$Signature = urlencode($hmac_sha1_base64);
			return $Signature;
		}
		
		private function auth(){
				  $urlStr = "http://".$this->restServer."/rtc/auth/valid?callback=test&access_key=".$this->accessKey."&digest=" . $this->signature;
				  $re = $this->sockGet($urlStr);
				  if(!is_null($re)  && $re != "" ){
			              $json = json_decode($re);
						  $ret = $json->{'ret'};
						  if(0 == $ret){
					    			  $random = $json->{'random'};
						          $key = $json->{'key'};
					    			  $accessTocken =$this->getSignature($random.":".$key, $this->secretKey);
					    			  return $accessTocken;
						  }else{
						  	   $msg = $json->{'msg'};
						  	   throw new AuthException($msg,$ret);
						  }
				  }else{
				  	  throw new AuthException('un authored',401);
				  } 
		}

		
		
		private function sockGet($url){
			 $re = file_get_contents($url);
             return $re;
		}
 }



  class stdObject {
     public function __construct(array $arguments = array()) {
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                $this->{$property} = $argument;
            }
        }
     }
  }
  


 class  AuthException extends Exception{
	 // 重定义构造器使 message 变为必须被指定的属性
    public function __construct($message,$code = 0) {
        	
        // 自定义的代码
        // 确保所有变量都被正确赋值
        parent::__construct($message, $code);
    }
	
    // 自定义字符串输出的样式
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
	
	public function errorMessage() {
	 	$error  = new stdObject();
        $error->code = $this->code;
        $error->message = $this->message;
        return $error;
	}
}
 
  class  RoomException extends Exception{
	 // 重定义构造器使 message 变为必须被指定的属性
    public function __construct($message,$code = 0) {
        	
        // 自定义的代码
        // 确保所有变量都被正确赋值
        parent::__construct($message, $code);
    }
	
    // 自定义字符串输出的样式
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
	
	public function errorMessage() {
	 	$error  = new stdObject();
        $error->code = $this->code;
        $error->message = $this->message;
        return $error;
	}
}	

?>
