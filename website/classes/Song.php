<?php
class Song {

	private $id;
	private $artist;
	private $title;
	private $album;
	private $cover;

	public function __construct($id, $artist, $title, $album, $cover) {
		$this->id = $id;
		$this->artist = $artist;
		$this->title = $title;
		$this->album = $album;
		$this->cover = $cover;
	}

	public function getId() {
		return $this->id;
	}

	public function getArtist() {
		return $this->smartQuotes($this->artist);
	}

	public function getTitle() {
		return $this->smartQuotes($this->title);
	}

	public function getAlbum() {
		return $this->smartQuotes($this->album);
	}

	public function getCover() {
		return $this->cover;
	}

	public function showCover() {
		if(!file_exists('images/covers/' . $this->id . '.jpg')) {
			return 'images/covers/0.jpg?' . md5_file('images/covers/0.jpg');
		}
		return 'images/covers/' . $this->id . '.jpg?' . md5_file('images/covers/' . $this->id . '.jpg');
	}

	private function smartQuotes($string) {
		$search = array(chr(145), chr(146), chr(147), chr(148));
		$replace = array('\'', '\'', '"', '"');
		return str_replace($search, $replace, $string);
	}
}
?>