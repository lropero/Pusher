<?php
class Pusher {

	private $server = 'localhost';
	private $username = 'username';
	private $password = 'password';
	private $database = 'samdb';

	private $string = '';
	private $url = 'http://www.trece.fm/update.php';

	public function __construct() {
		$array = $this->fetch();
		if(is_array($array)) {
			$this->string = base64_encode(gzdeflate(serialize($array), 9));
		}
	}

	private function fetch() {

		if(@mysql_connect($this->server, $this->username, $this->password)) {
			if(mysql_select_db($this->database)) {

				$history = array();
				$sql = 'SELECT songID, artist, title, album, picture FROM historylist WHERE duration > 60000 ORDER BY ID DESC LIMIT 11';
				$result = mysql_query($sql);
				while($row = mysql_fetch_assoc($result)) {
					$song = $this->getSong($row);
					if(!isset($now)) {
						$now = $song;
					} else {
						$history[] = $song;
					}
				}

				$queue = array();
				$sql = 'SELECT songlist.ID as songID, songlist.artist, songlist.title, songlist.album, songlist.picture FROM queuelist, songlist WHERE queuelist.songID = songlist.ID AND songlist.duration > 60000 ORDER BY queuelist.sortID LIMIT 10';
				$result = mysql_query($sql);
				while($row = mysql_fetch_assoc($result)) {
					$song = $this->getSong($row);
					$queue[] = $song;
				}

				return array($history, $now, $queue);
			}
		}

		return false;
	}

	private function getSong($row) {

		$id = isset($row['songID']) ? $row['songID'] : 0;
		$artist = isset($row['artist']) ? ucwords(strtolower(trim($row['artist']))) : '';
		$title = isset($row['title']) ? ucwords(strtolower(trim($row['title']))) : '';
		$album = isset($row['album']) ? ucwords(strtolower(trim($row['album']))) : '';
		$cover = '';

		$picture = isset($row['picture']) ? strtoupper(trim($row['picture'])) : '';
		if(strlen($picture)) {

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_NOBODY, true);
			curl_setopt($curl, CURLOPT_URL, 'http://www.trece.fm/images/covers/' . $id . '.jpg');
			curl_exec($curl);
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

			if(strcmp($code, '200')) {
				if(file_exists('c:/xampp/htdocs/push/covers/' . $id . '.jpg')) {
					$cover = base64_encode(gzdeflate(file_get_contents('c:/xampp/htdocs/push/covers/' . $id . '.jpg')));
				} elseif(file_exists('c:/Users/charlie/AppData/Local/SpacialAudio/SAMBC/samHTMLweb/pictures/' . $picture)) {
					imageResize('c:/Users/charlie/AppData/Local/SpacialAudio/SAMBC/samHTMLweb/pictures/' . $picture, 'c:/xampp/htdocs/push/covers/' . $id . '.jpg', 343, 343, true);
					$cover = base64_encode(gzdeflate(file_get_contents('c:/xampp/htdocs/push/covers/' . $id . '.jpg')));
				}
			}
		}

		$song = new Song($id, $artist, $title, $album, $cover);
		return $song;
	}

	public function push() {
		if(strlen($this->string)) {
			$string = file_exists('songlist.db') ? file_get_contents('songlist.db') : '';
			if(strcmp($this->string, $string)) {
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, 'string=' . urlencode($this->string));
				curl_setopt($curl, CURLOPT_URL, $this->url);
				if(curl_exec($curl)) {
					file_put_contents('songlist.db', $this->string);
				}
				curl_close($curl);
			}
		}
	}
}
?>