<?php
class Song {

	private $id;
	private $artist;
	private $title;
	private $album;
	private $cover;

	public function __construct($id, $artist, $title, $album) {
		$this->id = $id;
		$this->artist = $artist;
		$this->title = $title;
		$this->album = $album;
		$this->cover = '';
	}

	public function getId() {
		return $this->id;
	}

	public function setCover($cover) {
		$this->cover = $cover;
	}
}
