<?php
class Song {

	private $id;
	private $artist;
	private $title;
	private $album;
	private $cover;

	public function __construct($id, $artist, $title, $album) {
		$this->id = utf8_encode($id);
		$this->artist = utf8_encode($artist);
		$this->title = utf8_encode($title);
		$this->album = utf8_encode($album);
		$this->cover = '';
	}

	public function getId() {
		return $this->id;
	}

	public function setCover($cover) {
		$this->cover = utf8_encode($cover);
	}
}
