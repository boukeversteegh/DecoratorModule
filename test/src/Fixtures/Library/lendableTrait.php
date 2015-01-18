<?php

namespace Fixtures\Library;

trait lendableTrait {
	
	protected $lending_user;

	public function lendTo(\Fixtures\Entity\User $user) {
		$this->lending_user = $user;
	}

	public function getLendingTo()
	{
		return $this->lending_user;
	}
}