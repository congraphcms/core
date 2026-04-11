<?php

use Congraph\Core\Repositories\Model;

class GuardedModel extends Model
{
	protected $guarded = ['secret'];
}
