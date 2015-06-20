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

	use ErrorManagerTrait;

	/**
	 * Validator factory
	 *
	 * @var Illuminate\Validation\Factory
	 */
	protected $validatorFactory;


	/**
	 * Validate params by given rules
	 * 
	 * @param $params 		array 	- input parameters
	 * @param $rules 		array 	- rules for validator
	 * @param $clean 		boolean - whether to unset params that are not in rules
	 * 
	 * @return boolean
	 */      
	protected function validateParams(array &$params, array $rules, $clean = false)
	{

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

		// init laravel validator
		$validator = $this->getValidator($params, $rules);

		if ($validator->fails())
		{
			// params did not pass validation rules
			
			// get validator errors
			$messages = $validator->messages();

			

			// add errors to bag
			$this->addErrors($messages);

			return false;
		}
		else
		{
			// params passed validation rules
			return true;
		}
	}

	/**
	 * Set validator factory
	 * 
	 * @param Illuminate\Contracts\Validation\Factory $factory
	 * 
	 * @return void
	 */ 
	public function setValidatorFactory(ValidatorFactoryContract $factory){
		$this->validatorFactory = $factory;
	}

	/**
	 * Get validator instance
	 * 
	 * @param array $params
	 * @param array $rules
	 * 
	 * @return Illuminate\Validation\Validator
	 */      
	public function getValidator(array $params, array $rules)
	{

		// get validator instance
		return $this->validatorFactory->make($params, $rules);
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
}
