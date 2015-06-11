<?php
/**
 * @addtogroup StreamOneSDK
 * @{
 */

namespace StreamOne\API\v3;

/**
 * Class to generate password-related data
 */
class Password
{
	/**
	 * Generate a password response for a given password, salt and challenge from the server
	 *
	 * @param $password
	 * @param $salt
	 * @param $challenge
	 * @return int
	 */
	public static function generatePasswordResponse($password, $salt, $challenge)
	{
		$password_hash = crypt(md5($password), $salt);
		$sha_password_hash = hash('sha256', $password_hash);
		$password_hash_with_challenge = hash('sha256', $sha_password_hash . $challenge);
		return base64_encode($password_hash_with_challenge ^ $password_hash);
	}

	/**
	 * This function will generate a password hash based on the API version 2 password hashing system
	 *
	 * This is used if the session/initialize API responds that one should send the API version 2 password.
	 * This is only needed once for every user, as the StreamOne platform will convert the user password to the API
	 * version 3 system automatically afterwards
	 *
	 * @param string $password
	 *   The plain text password of the user
	 *
	 * @return string
	 *   The hashed password
	 */
	public static function generateV2PasswordHash($password)
	{
		return md5($password);
	}
}

/**
 * @}
 */
