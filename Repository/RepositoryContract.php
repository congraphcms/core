<?php 
/*
 * This file is part of the Cookbook package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Repository;

/**
 * Interface for any repository in Cookbook package
 * 
 * These are just basic functions that any repository should have.
 * This interface will be implemented by abstract repository.
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	Cookbook/Core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
interface RepositoryContract
{
	
	/**
	 * Inserting model into database
	 * @param $model array | object with parameters for object creation.
	 */
	public function create($model);

	/**
	 * Updating model in database
	 * @param $model array | object with parameters for object update.
	 */
	public function update($model);

	/**
	 * Deleting model with given ID from database
	 * @param $id int ID of object to be deleted.
	 */
	public function delete($id);
}