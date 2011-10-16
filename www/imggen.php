<?php
use Nette\Image;
use Nette\Diagnostics\Debugger;
require_once '../lib/Nette/loader.php';

function renderHex ($name, $type, $color, $borderColor)
{
	$img = Image::fromBlank(60, 40, Image::rgb(255, 255, 255, 255));
	$border = Image::fromBlank(60, 40, Image::rgb(255, 255, 255, 255));
	$shadow = Image::rgb(66, 66, 66, 50);
	$border->filledPolygon(array(
		0, 19,
		14, 0,
		45, 0,
		59, 19,
		59, 21,
		45, 39,
		14, 39,
		0, 21,
		0, 20,
		4, 20,
		17, 37,
		42, 37,
		55, 20,
		43, 3,
		16, 3,
		4, 20
	), 16, $shadow);

	$border->filledPolygon(array(
		0, 20,
		15, 39,
		44, 39,
		59, 20,
		44, 0,
		15, 0,
		0, 20,
		2, 20,
		15, 2,
		44, 2,
		57, 20,
		43, 38,
		16, 38,
		2, 20,
		0, 20
	), 14, $borderColor);
	$img->filledPolygon(array(
		3, 20,
		16, 2,
		44, 2,
		56, 20,
		44, 37,
		16, 37
	), 6, $color);
	$img->place($border, 0, 0);
	$img->save("images/fields/gen/hex_{$name}_{$type}.png", 9, Image::PNG);
}

function renderHexWithResize ($name, $type, $color, $borderColor)
{
	$img = Image::fromBlank(600, 400, Image::rgb(255, 255, 255, 255));
	$border = Image::fromBlank(600, 400, Image::rgb(255, 255, 255, 255));
	$shadow = Image::rgb(66, 66, 66, 50);
	$border->filledPolygon(array(
		0, 190,
		140, 0,
		450, 0,
		599, 190,
		599, 210,
		450, 399,
		140, 399,
		0, 210,
		0, 190,
		40, 200,
		170, 370,
		420, 370,
		562, 200,
		430, 30,
		160, 30,
		40, 200
	), 16, $shadow);

	$border->filledPolygon(array(
		0, 200,
		150, 399,
		440, 399,
		599, 200,
		440, 0,
		150, 0,
		0, 200,
		20, 200,
		150, 20,
		440, 20,
		580, 200,
		430, 380,
		160, 380,
		20, 200,
		0, 200
	), 14, $borderColor);
	$img->filledPolygon(array(
		0, 200,
		150, 0,
		440, 0,
		599, 200,
		440, 399,
		150, 399
	), 6, $color);
	$img->place($border, 0, 0);
	$img->resize(60, 40);
	$img->save("images/fields/gen/hex_{$name}_{$type}.png", 9, Image::PNG);
}

$images = array(
	'barren' => Image::rgb(209, 177, 145),
	'forest' => Image::rgb(88, 135, 87),
	'lake' => Image::rgb(95, 136, 183),
	'meadow' => Image::rgb(144, 205, 131),
	'moor' => Image::rgb(0, 156, 128),
	'mountain' => Image::rgb(94, 82, 60),
	'plain' => Image::rgb(254, 255, 147),
);

$types = array(
	'player' => Image::rgb(0, 255, 0),
	'neutral' => Image::rgb(0, 0, 0),
	'ally' => Image::rgb(0, 0, 255),
	'enemy' => Image::rgb(255, 0, 0)
);

foreach ($images as $name => $color) {
	foreach ($types as $type => $borderColor) {
		renderHexWithResize($name, $type, $color, $borderColor);
	}
}

// renderHexWithResize('forest', 'neutral', $images['forest'], $types['neutral']);