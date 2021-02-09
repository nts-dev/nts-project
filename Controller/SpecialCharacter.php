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
            utf8_decode('¥µְֱֲֳִֵֶַָֹÊֻּֽ־ֿ׀ׁׂ׃װױײ״ÙÚÛÜÝßאבגדהוזחטיךכלםמןנסעףפץצרשתûü‎ÿ'),
            'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
    }

	function cleanText($str){
		$str = str_replace("AE"," ", $str);
		$str = str_replace("°","&deg;", $str);
		$str = str_replace("¹³"," ", $str);
		$str = str_replace("±","&plusmn;", $str);
		$str = str_replace("µ","&mu;", $str);
		$str = str_replace("ה","&#228;", $str);
		$str = str_replace("ׁ" ,"&#209;", $str);
		$str = str_replace("ס" ,"&#241;", $str);
		$str = str_replace("ס" ,"&#241;", $str);
		$str = str_replace("ֱ","&#193;", $str);
		$str = str_replace("ב","&#225;", $str);
		$str = str_replace("ֹ","&#201;", $str);
		
		$str = str_replace("י","&#233;", $str);
		$str = str_replace("צ","&#0246", $str);
		$str = str_replace("ת","&#250;", $str);

		$str = str_replace("ש","&#249;", $str);
		$str = str_replace("ֽ","&#205;", $str);
		$str = str_replace("ם","&#237;", $str);
		$str = str_replace("׃","&#211;", $str);
		$str = str_replace("ף","&#243;", $str);
		$str = str_replace("“","&#8220;", $str);

		$str = str_replace("”","&#8221;", $str);

		$str = str_replace("‘","&#8216;", $str);
		$str = str_replace("’","&#8217;", $str);
		$str = str_replace("—","&#8212;", $str);

		$str = str_replace("–","&#8211;", $str);
		$str = str_replace("™","&trade;", $str);
		$str = str_replace("ü","&#252;", $str);
		$str = str_replace("Ü","&#220;", $str);
		$str = str_replace("Ê","&#202;", $str);
		$str = str_replace("ך","&#238;", $str);
		$str = str_replace("ַ","&#199;", $str);
		$str = str_replace("ח","&#231;", $str);
		$str = str_replace("ָ","&#200;", $str);
		$str = str_replace("ט","&#232;", $str);
		$str = str_replace("•","&#149;" , $str);
		
		
		$str = str_replace("¢","&#162;", $str);
		$str = str_replace("£","&#163;", $str);
		$str = str_replace("כ","&#235;", $str);
		$str = str_replace("ך","&#234;", $str);
		$str = str_replace("ש","&#249;", $str);
		$str = str_replace("ת","&#250;", $str);
		$str = str_replace("û","&#251;", $str);
		$str = str_replace("ü","&#252;", $str);
		$str = str_replace("ß","&#223;", $str);
		$str = str_replace("ן","&#239;", $str);
	
		return $str;
	}
}