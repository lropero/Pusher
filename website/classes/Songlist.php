<?php
class Songlist {

	private $filename = 'songlist.db';
	private $history;
	private $now;
	private $queue;

	public function __construct() {
		if(file_exists($this->filename)) {
			list($this->history, $this->now, $this->queue) = unserialize(gzinflate(base64_decode(file_get_contents($this->filename))));
		}
	}

	public function extractCovers() {
		$songs = array_merge($this->history, array($this->now), $this->queue);
		foreach($songs as $song) {
			if(!file_exists('images/covers/' . $song->getId() . '.jpg')) {
				if(strlen($song->getCover())) {
					file_put_contents('images/covers/' . $song->getId() . '.jpg', gzinflate(base64_decode($song->getCover())));
				}
			}
		}
	}

	public function generateChecksum() {
		if(is_array($this->history)) {
			$songs = array_merge($this->history, array($this->now), $this->queue);
			return sha1(serialize($songs));
		}
		return false;
	}

	public function getHistory() {
		return $this->history;
	}

	public function getNow() {
		return $this->now;
	}

	public function getQueue() {
		return $this->queue;
	}
}
?>