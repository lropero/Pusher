<?php
define('API_KEY', 'CHANGE_ME');
define('API_URL', 'http://www.trece.fm/api/');
define('DB_DATABASE', 'samdb');
define('DB_PASSWORD', 'CHANGE_ME');
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'CHANGE_ME');
define('SAM_PICTURES', 'C:/Users/Charlie/AppData/Local/SpacialAudio/SAMBC/samHTMLweb/pictures/');

class Pusher {

	private $forcePost;
	private $pictures;
	private $songlist;

	public function __construct() {

		$this->forcePost = false;
		$this->pictures = [];
		$this->songlist = $this->fetchSonglist();

		if(is_array($this->songlist)) {

			$songs = array_merge($this->songlist['history'], array($this->songlist['now']), $this->songlist['queue']);
			$checksumLocal = sha1(serialize($songs));
			$checksumRemote = $this->apiGetChecksum();

			$this->addCoversIfNeeded();

			if(($checksumRemote && strcmp($checksumLocal, $checksumRemote)) || $this->forcePost) {
				$this->apiPostSonglist();
			}
		}
	}

	private function addCover($song) {

		$cover = '';

		$id = $song->getId();
		$picture = $this->pictures[$id];

		if(file_exists(__DIR__ . '/covers/' . $id . '.jpg')) {
			$cover = base64_encode(gzdeflate(file_get_contents(__DIR__ . '/covers/' . $id . '.jpg')));
		} elseif(file_exists(SAM_PICTURES . $picture)) {
			imageResize(SAM_PICTURES . $picture, __DIR__ . '/covers/' . $id . '.jpg', 320, 320, true);
			$cover = base64_encode(gzdeflate(file_get_contents(__DIR__ . '/covers/' . $id . '.jpg')));
			$this->forcePost = true;
		}

		$song->setCover($cover);
	}

	private function addCoversIfNeeded() {

		$parameters = '';
		$ids = array_keys($this->pictures);
		foreach($ids as $id) {
			$parameters .= '/' . $id;
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Auth: ' . API_KEY));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, API_URL . 'cover' . $parameters);
		$response = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if(!strcmp($code, '200')) {
			$ids = json_decode($response, true);
			foreach($this->songlist as $songs) {
				if(is_array($songs)) {
					foreach($songs as $song) {
						if(in_array($song->getId(), $ids) || !file_exists(__DIR__ . '/covers/' . $song->getId() . '.jpg')) {
							$this->addCover($song);
						}
					}
				} else {
					$song = $songs;
					if(in_array($song->getId(), $ids) || !file_exists(__DIR__ . '/covers/' . $song->getId() . '.jpg')) {
						$this->addCover($song);
					}
				}
			}
		}
	}

	private function apiGetChecksum() {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Auth: ' . API_KEY));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, API_URL . 'checksum');
		$response = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		return (!strcmp($code, '200') || !strcmp($code, '404')) ? (strlen($response) ? $response : true) : false;
	}

	private function apiPostSonglist() {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Auth: ' . API_KEY));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'base64=' . urlencode(base64_encode(gzdeflate(serialize($this->songlist), 9))));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, API_URL . 'songlist');
		$response = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		return !strcmp($code, '200') ? $response : false;
	}

	private function fetchSonglist() {

		if(@mysql_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD) && mysql_select_db(DB_DATABASE)) {

			if(date('H') == 0 && date('i') == 0) {
				$sql = 'REPAIR TABLE adz, category, categorylist, disk, event, eventtime, fixedlist, fixedlist_item, historylist, queuelist, requestlist, songlist';
				mysql_query($sql);
			}

			$history = array();
			$sql = 'SELECT songID, artist, title, album, picture FROM historylist WHERE duration > 60000 ORDER BY ID DESC LIMIT 6';
			$result = mysql_query($sql);
			while($row = mysql_fetch_assoc($result)) {
				$song = $this->getSong($row);
				if($song) {
					if(!isset($now)) {
						$now = $song;
					} else {
						$history[] = $song;
					}
				}
			}

			$queue = array();
			$sql = 'SELECT songlist.ID as songID, songlist.artist, songlist.title, songlist.album, songlist.picture FROM queuelist, songlist WHERE queuelist.songID = songlist.ID AND songlist.duration > 60000 ORDER BY queuelist.sortID LIMIT 5';
			$result = mysql_query($sql);
			while($row = mysql_fetch_assoc($result)) {
				$song = $this->getSong($row);
				if($song) {
					$queue[] = $song;
				}
			}
		}

		return isset($now) ? array('history' => $history, 'now' => $now, 'queue' => $queue) : false;
	}

	private function getSong($row) {

		if(!isset($row['songID'])) {
			return false;
		}

		$id = $row['songID'];
		$artist = isset($row['artist']) ? ucwords(strtolower(trim($row['artist']))) : '';
		$title = isset($row['title']) ? ucwords(strtolower(trim($row['title']))) : '';
		$album = isset($row['album']) ? ucwords(strtolower(trim($row['album']))) : '';

		$picture = isset($row['picture']) ? trim($row['picture']) : '';
		if(strlen($picture)) {
			$this->pictures[$id] = $picture;
		}

		$song = new Song($id, $artist, $title, $album);
		return $song;
	}
}
