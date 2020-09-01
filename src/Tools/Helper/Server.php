<?php 

namespace Tools\Helper;

use Mobile_Detect;

class Server
{
	public static function isMobile()
	{
		$detect = new Mobile_Detect();
		
		if($detect->isMobile()){
			return true;
		}else{
			return false;
		}
	}
}