<?php 
/*
 * This file is part of the Cookbook package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Traits;

use Illuminate\Contracts\Validation\Factory as ValidatorFactoryContract;
use Cookbook\Contracts\Eav\FieldValidatorFactoryContract;
use Cookbook\Core\Exceptions\ValidationException;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Bus\Command;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator as LaravelValidator;

/**
 * Trait for validating input parameters 
 * uses Laravel Validator for validation
 * 
 * @uses 		Illuminate\Contracts\Validation\Factory
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	Cookbook/Core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
trait ValidatorTrait
{

	/**
	 * validation exception that will be thrown if validation fails
	 *
	 * @var Cookbook\Core\Exceptions\ValidationException
	 */
	protected $exception = null;

	/**
	 * Validator
	 * 
	 * @var \Illuminate\Support\Facades\Validator
	 */
	protected $validator;

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
			$validator = $this->newValidator($params, $rules, $clean);
			$this->setValidator($validator);
		}
		$validator = $this->getValidator();

		$rules = $validator->getRules();
		

		if ($validator->fails())
		{
			// params did not pass validation rules
			
			// add errors to exception
			$this->getException()->addErrors($validator->errors()->toArray());

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
			if(is_array($rule))
			{
				$rule = $this->addUniqueRuleException($rule, $id);
				continue;
			}
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
	public function newValidator(array &$params, array $rules, $clean = true)
	{
		// if validating update params 
		// unique rule should skip entry with this id
		
		// check if these are update params
		if(!empty($params['id']))
		{

			// add exception for this id on all unique rules
			$rules = $this->addUniqueRuleException($rules, $params['id']);
		}

		if($clean)
		{
			$params = $this->cleanParams($params, $rules);
		}
		
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
	 * Get exception instance
	 * 
	 * @param \Illuminate\Validation\Validator $validator
	 * 
	 * @return void
	 */      
	public function getException()
	{
		if($this->exception == null) {
			$this->exception = new ValidationException();
		}

		return $this->exception;
	}


	/**
	 * Validate command
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	abstract public function validate(Command $command);
	
}