<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Validation;

use Cookbook\Contracts\Eav\FieldValidatorFactoryContract;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Bus\RepositoryCommand;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator as LaravelValidator;


/**
 * Abstract Validator class
 * 
 * Validating commands
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class Validator
{

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception;

	/**
	 * Validator
	 * 
	 * @var \Illuminate\Support\Facades\Validator
	 */
	protected $validator;

	/**
	 * Create new Validator
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->exception = new ValidationException();
	}

	/**
	 * Validate params by given rules
	 * 
	 * @param $params 		array 	- input parameters
	 * @param $rules 		array 	- rules for validator
	 * @param $clean 		boolean - whether to unset params that are not in rules
	 * 
	 * @return boolean
	 */      
	protected function validateParams(array &$params, $rules = null, $clean = false)
	{

		if( ! is_null($rules) )
		{
			// init laravel validator
			$validator = $this->newValidator($params, $rules);
			$this->setValidator($validator);
		}
		$validator = $this->getValidator();

		$rules = $validator->getRules();
		
		if($clean)
		{
			$params = $this->cleanParams($params, $rules);
		}

		// if validating update params 
		// unique rule should skip entry with this id
		
		// check if these are update params
		if(!empty($params['id']))
		{

			// add exception for this id on all unique rules
			$rules = $this->addUniqueRuleException($rules, $params['id']);
		}

		
		

		if ($validator->fails())
		{
			// params did not pass validation rules
			
			// add errors to exception
			$this->exception->addErrors($validator->errors()->toArray());

			return false;
		}
		else
		{
			// params passed validation rules
			return true;
		}
	}

	/**
	 * Clean params from all unwanted values by intersecting
	 * rules array with params array
	 * 
	 * @param array $params
	 * @param array $rules
	 * 
	 * @return array
	 */      
	protected function cleanParams(array $params, array $rules)
	{

		// intersect arrays
		$params = array_intersect_key($params, $rules);

		return $params;
	}

	/**
	 * Add exception to any unique rules for given object id
	 * 
	 * @param array $params
	 * @param array $rules
	 * 
	 * @return array
	 */      
	protected function addUniqueRuleException(array $rules, $id)
	{

		// update all unique rules
		foreach ($rules as $key => &$rule)
		{
			// check for unique rule
			$unique_pos = strpos($rule, 'unique:');
			// if rule has unique restriction
			if($unique_pos !== false)
			{

				// find if there is other rules after unique rule
				// if there are put cursor between these rules, 
				// otherwise put it on the end of string
				$next_rule_pos = strpos($rule, '|', $unique_pos);
				if($next_rule_pos !== false)
				{
					$insert_pos = $next_rule_pos;
				}
				else
				{
					$insert_pos = strlen($rule);
				}

				// add exception for this id
				$rule = substr_replace($rule, ',' . $id . ',id', $insert_pos, 0);
			}
		}

		return $rules;

	}

	/**
	 * Get validator instance
	 * 
	 * @return Illuminate\Validation\Validator
	 */      
	public function getValidator()
	{
		return $this->validator;
	}

	/**
	 * Set new validator instance
	 * 
	 * @param array $params
	 * @param array $rules
	 * 
	 * @return Illuminate\Validation\Validator
	 */      
	public function newValidator(array $params, array $rules)
	{
		return ValidatorFacade::make($params, $rules);
	}

	/**
	 * Get validator instance
	 * 
	 * @param \Illuminate\Validation\Validator $validator
	 * 
	 * @return void
	 */      
	public function setValidator(LaravelValidator $validator)
	{
		$this->validator = $validator;
	}


	/**
	 * Validate command
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	abstract public function validate(RepositoryCommand $command);
	
}