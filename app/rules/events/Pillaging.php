<?php
namespace Rules\Events;
use Entities;

class Pillaging extends Attack
{
	public function getDescription ()
	{
		return 'Loupežný útok';
	}
	
	public function formatReport ($data)
	{
		return $data;
	}
}