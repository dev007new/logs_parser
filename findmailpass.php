<?php


if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}
class LogsParse {
	
	private $passfile = "";
	private $isParsePasswords;
	private $excludeDomains = [];

	public function __construct($excludeDomains = [], $passfile = "Passwords.txt", $parsePasswords = FALSE) {
		$this->passfile = $passfile;
		$this->isParsePasswords = $parsePasswords;
		$this->excludeDomains = $excludeDomains;
	}


	private function parsePasswords($filename) {

		$content = file_get_contents($filename);
		if(preg_match_all("/Password: ([^\n]+)/", $content, $matches)) {

			return array_unique($matches[1]);
		}
		return [];
	}

	public function listFolderFiles($dir) {

		$ffs = scandir($dir);

		//убираем из списка текущий каталог
		unset($ffs[array_search('.', $ffs, true)]);

		//убираем из списка каталог выше
		unset($ffs[array_search('..', $ffs, true)]);

		foreach ($ffs as $ff) {

			if(is_dir($dir.'/'.$ff)) {
			   $this->listFolderFiles($dir.'/'.$ff);

		}
		elseif($ff == $this->passfile) {

			$passwords = [];
			if($this->isParsePasswords) {
				$passwords = $this->parsePasswords($dir.'/'.$ff);
			}
			return $this->parseFile($dir.'/'.$ff, $passwords);
		}
		// для всех остальных файлов ничего не делаем
		else {
			//parseFile($dir.'/'.$ff);

		}
    }
}

	function parseFile($ff,$passwords = []) {

		$content = file_get_contents($ff);

		preg_match_all("([a-zA-Z]+@([a-zA-Z]+\.[\.a-zA-Z]+))", $content, $matches);

		$emails = [];

		foreach($matches[1] as $key => $domain) {

			$contains = FALSE;
			foreach($this->excludeDomains as $bigdomain) {
				if(str_contains(strtolower($domain), $bigdomain))
					$contains = TRUE;

			}

			if(!$contains)
				$emails[] = strtolower($matches[0][$key]);
		}
		$emails = array_unique($emails);

		foreach($emails as $email) {
			if($this->isParsePasswords) {
				foreach($passwords as $password) {
					echo $email.":".$password."\n";
				}
			}
			else {
					echo $email."\n";
			}
		}
	}
}

// домены которые нужно отсеять
//$excludeDomains = ["hotmail","gmail","yahoo"];
$excludeDomains = [];
// файл, в котором находятся емейлы и пароли
$passfile = "Passwords.txt";
// добавлять пароли к емейлам
$parsePasswords = TRUE;

$logsParse = new LogsParse($excludeDomains, $passfile, $parsePasswords);
$logsParse->listFolderFiles('logs2');

?>
