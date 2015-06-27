<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Bus;

use Closure;
use Exception;
use Illuminate\Http\Response;
use Cookbook\Core\Exceptions\Exception as CookbookException;

/**
 * ApiCommandDispatcher class
 * 
 * Cookbook API Command Dispatcher
 * 
 * @uses  		Cookbook\Core\Bus\CommandDispatcher
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ApiCommandDispatcher extends CommandDispatcher
{

	/**
	 * Dispatch a command to its appropriate handler.
	 * And handle the result as JSON response for API
	 *
	 * @param  mixed  $command
	 * @param  Closure|null  $afterResolving
	 * 
	 * @return mixed
	 */
	public function dispatch($command, Closure $afterResolving = null)
	{
		try
		{
			// dispatch the command
			$result = parent::dispatch($command, $afterResolving);
		}
		catch(Exception $e)
		{
			return $this->handleException($e);
		}

		// return the handler result
		return $this->createResponse($result);
	}

	protected function handleException(Exception $e)
	{
		// if it's a cookbook exception, 
		// use it's toArray function to ger all errors
		if( $e instanceOf CookbookException )
		{
			return $this->handleCookbookException($e);
		}

		// if it's some other exception return 500 error from exception
		return $this->handleGenericException($e);
	}

	protected function handleCookbookException(CookbookException $e)
	{
		$errors = $e->toArray();

		$responseCode = $this->getResponseCodeFromErrors($errors);

		return $this->createResponse(['errors' => $errors], $responseCode);
	}

	protected function handleGenericException(Exception $e)
	{
		$message = $e->getMessage();
		$code = $e->getCode();
		$status = 500;

		$error = [
			'status' 	=> $status,
			'code'	 	=> $code,
			'message' 	=> $message,
			'pinter' 	=> '/'
		];

		$errors = [$error];

		return $this->createResponse(['errors' => $errors], $status);
	}

	protected function getResponseCodeFromErrors(array $errors)
	{
		$statusDetail = 0;
		$statusGeneral = 0;
		foreach ($errors as $error) {
			
			// if it's first error set values and move on
			if($statusDetail = 0)
			{
				$statusGeneral = $statusDetail = $error['status'];
				continue;
			}

			// get http status code group
			$statusGroup = floor($error['status'], -2);

			// check if group is lower then previous error
			// if it is break
			if($statusGroup < floor($statusGeneral, -2))
			{
				break;
			}
			// check if errors are different
			// if they are return general http code (400, 500...)
			if($statusDetail != $error['status'])
			{
				$statusGeneral = $statusGroup;
			}
		}

		return $statusGeneral;
	}

	public function createResponse($data = [], $code = 200, $headers = [])
	{
		$response = new Response(json_encode($data), $code);

		$response->header('Content-Type', 'application/cookbook.api+json');

		if( ! empty($headers) && is_array($headers) )
		{
			foreach ($headers as $key => $value) {
				$response->header($key, $value, true);
			}
		}
	}

}