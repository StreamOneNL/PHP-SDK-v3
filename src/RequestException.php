<?php

namespace StreamOne\API\v3;

/**
 * Exception thrown if an error occurred while communicating with the API.
 *
 * This exception should be thrown when code cannot be executed because communication with the
 * API failed. It is not thrown from Request itself, but must be thrown from code using that class.
 */
class RequestException extends \RuntimeException
{
	/**
	 * Create an RequestException from a Request
	 *
	 * @param Request $request
	 *   The request to create an exception from
	 * @return RequestException
	 *   The exception corresponding to the given request
	 */
	public static function fromRequest(Request $request)
	{
		return new RequestException($request->statusMessage(), $request->status());
	}
}
