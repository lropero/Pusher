<?php
function imageResize($src, $dst, $dst_width = 0, $dst_height = 0, $crop = false) {
	if(file_exists($src) || strpos($src, "://") !== false) {
		$image_types = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
		$image_size = getimagesize($src);
		if(in_array($image_size[2], $image_types)) {
			$src_width = $image_size[0];
			$src_height = $image_size[1];
			if($dst_width == 0) {
				$dst_width = round($src_width * $dst_height / $src_height);
				$dst_width = $dst_width == 0 ? 1 : $dst_width;
				$crop = false;
			}
			if($dst_height == 0) {
				$dst_height = round($src_height * $dst_width / $src_width);
				$dst_height = $dst_height == 0 ? 1 : $dst_height;
				$crop = false;
			}
			if($crop) {
				$dst_width2 = $dst_width;
				$dst_height2 = $dst_height;
				$x = 0;
				$y = 0;
				$src_ratio = $src_width / $src_height;
				$dst_ratio = $dst_width / $dst_height;
				if($src_ratio > $dst_ratio) {
					$dst_width = round($src_width * $dst_height / $src_height);
					$dst_width = $dst_width == 0 ? 1 : $dst_width;
					$x = abs(round(($dst_width - $dst_width2) / 2));
				}
				elseif($src_ratio < $dst_ratio) {
					$dst_height = round($src_height * $dst_width / $src_width);
					$dst_height = $dst_height == 0 ? 1 : $dst_height;
					$y = abs(round(($dst_height - $dst_height2) / 2));
				}
			}
			$new = imagecreatetruecolor($dst_width, $dst_height);
			$image = imagecreatefromstring(file_get_contents($src));
			imagecopyresampled($new, $image, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
			if($crop) {
				$new2 = imagecreatetruecolor($dst_width2, $dst_height2);
				imagecopy($new2, $new, 0, 0, $x, $y, $dst_width2, $dst_height2);
				imagejpeg($new2, $dst, 70);
			}
			else {
				imagejpeg($new, $dst, 70);
			}
			return true;
		}
	}
	return false;
}
?>