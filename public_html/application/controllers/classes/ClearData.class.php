<?
class ClearData{
	public static function clearInt($data){
		$data = (int)(trim($data));
		return $data;
	}
	
	public static function clearStr($data){
		$data = strip_tags(trim($data));
		return $data;
	}
	
	public static function clearArray(array $arr){
		foreach($arr as $key=>$value){
			if(is_numeric($key)){
				$key = self::clearInt($key);
			}elseif(is_string($key)){
				$key = self::clearStr($key);
			}
			if(is_numeric($value)){
				$value = self::clearInt($value);
			}elseif(is_string($value)){
				$value = self::clearStr($value);
			}
			$arrClear[$key] = $value;	
		}
		if(!empty($arrClear))
			return $arrClear;
	}
}