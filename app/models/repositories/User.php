<?php
namespace Repositories;

class User extends BaseRepository
{
	/**
	 * Get current user
	 * @return Entities\User
	 */
	public function getActiveUser ()
	{
		return $this->find($this->context->user->id);
	}
}