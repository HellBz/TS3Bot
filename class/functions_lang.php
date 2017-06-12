<?php 
	class Language {
		private $lang = array();
		public function __construct() {
			require_once './includes/bot.lang.php';
			$this->lang = $lang;
		}
		
		public function __get($nazwa) {
			return $this->lang[$nazwa];
		}
		
		function sprintf($string){
			$array = func_get_args();
			$num_args = count($array);
			for($i = 1; $i < $num_args; $i++){
				$string = str_replace('{'.$i.'}', $array[$i], $string);
			}
			return $string;
		}
	}
?>
