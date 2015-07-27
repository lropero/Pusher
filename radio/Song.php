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
}
?>