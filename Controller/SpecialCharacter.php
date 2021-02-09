<?php
/**
 * 
 *
 *
 */
class SpecialCharacter{
	/**
	 * 
	 * @param string with the special character $str
	 * @return normalized string with html character
	 */
	  function cleanTextClear($str)
    {
        return strtr(utf8_decode($str), 
            utf8_decode('���������������������������������������������������������������������'),
            'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
    }

	function cleanText($str){
		$str = str_replace("AE"," ", $str);
		$str = str_replace("�","&deg;", $str);
		$str = str_replace("��"," ", $str);
		$str = str_replace("�","&plusmn;", $str);
		$str = str_replace("�","&mu;", $str);
		$str = str_replace("�","&#228;", $str);
		$str = str_replace("�" ,"&#209;", $str);
		$str = str_replace("�" ,"&#241;", $str);
		$str = str_replace("�" ,"&#241;", $str);
		$str = str_replace("�","&#193;", $str);
		$str = str_replace("�","&#225;", $str);
		$str = str_replace("�","&#201;", $str);
		
		$str = str_replace("�","&#233;", $str);
		$str = str_replace("�","&#0246", $str);
		$str = str_replace("�","&#250;", $str);

		$str = str_replace("�","&#249;", $str);
		$str = str_replace("�","&#205;", $str);
		$str = str_replace("�","&#237;", $str);
		$str = str_replace("�","&#211;", $str);
		$str = str_replace("�","&#243;", $str);
		$str = str_replace("�","&#8220;", $str);

		$str = str_replace("�","&#8221;", $str);

		$str = str_replace("�","&#8216;", $str);
		$str = str_replace("�","&#8217;", $str);
		$str = str_replace("�","&#8212;", $str);

		$str = str_replace("�","&#8211;", $str);
		$str = str_replace("�","&trade;", $str);
		$str = str_replace("�","&#252;", $str);
		$str = str_replace("�","&#220;", $str);
		$str = str_replace("�","&#202;", $str);
		$str = str_replace("�","&#238;", $str);
		$str = str_replace("�","&#199;", $str);
		$str = str_replace("�","&#231;", $str);
		$str = str_replace("�","&#200;", $str);
		$str = str_replace("�","&#232;", $str);
		$str = str_replace("�","&#149;" , $str);
		
		
		$str = str_replace("�","&#162;", $str);
		$str = str_replace("�","&#163;", $str);
		$str = str_replace("�","&#235;", $str);
		$str = str_replace("�","&#234;", $str);
		$str = str_replace("�","&#249;", $str);
		$str = str_replace("�","&#250;", $str);
		$str = str_replace("�","&#251;", $str);
		$str = str_replace("�","&#252;", $str);
		$str = str_replace("�","&#223;", $str);
		$str = str_replace("�","&#239;", $str);
	
		return $str;
	}
}