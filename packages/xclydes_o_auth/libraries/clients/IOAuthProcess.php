<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

interface IOAuthProcess{
	
	const ACTION_AUTHEMTICATE = 1;
	const ACTION_AUTHORIZE = 2;
	
	/**
	 * Gets an object representing the user on the current network
	 * based on the data set.
	 * The returned object should contain :- 
	 * 	'uid' 		=> The user's ID on the client network.
	 *  'uname' 	=> The user's name on the client network.
	 *  'image'		=> The URL to the user's profile image.
	 *  'name' 		=> The user's real name on the client network.
	 *  'profile'	=> The URL to the user's profile page on the client network.
	 *  'email'		=> The email address related to the user, if any.
	 *  'valid' 	=> Indication whether or not the user object should be treated as valid.
	 */
	public function getNetworkUser();
	
	/**
	 * Gets an array of string representing the permissions
	 * requested by the client, if applicable. 
	 */
	public function getScope();
	
	/**
	 * Sets the scope to be requested, if any.
	 * @param array|string $newScope The scope or
	 * set of attributes to be requested.
	 */
	public function setScope($newScope);
	
	
	/**
	 * Sets the OAuth Data Store which is to refrenced when searching for
	 * and storing information exchanged.
	 * @param OAuthDataStore $newStore The new datastore to be referenced.
	 */
	public function setOAuthDataStore($newStore);
	
	/**
	 * Gets the callback URL set on this instance 
	 * of the client.
	 */
	public function getCallback();
	
	/**
	 * Sets the callback URL to be used by this instance
	 * of the client.
	 * @param string $newCallback The new callback URL.
	 */
	public function setCallback($newCallback);
	
	/**
	 * Gets the OAuthConsumer which has been set on this instance
	 * of the client.
	 */
	public function getConsumer();
	
	/**
	 * Sets the consumer or details to be used by this instance
	 * of the client
	 * @param OAuthConsumer|String $consumer This value may be either
	 * the OAuthConsumer to be used, or the key assigned by the network.
	 * @param string $consumerSecret The secret to be used along with the
	 * key provided. This value is to be ignored if the first parameter
	 * is an OAuthConsumer.
	 */
	public function setConsumer($consumerOrKey, $consumerSecret = NULL);
	
	/**
	 * Gets the url to be used for the action specified.
	 * @param integer $action The value representing the desired
	 * action.
	 */
	public function getAuthURL($action = self::ACTION_AUTHEMTICATE);
	
	/**
	 * Perform the necessary functions to retrieve a
	 * request token.
	 * @param String $oauth_callback The oauth callback url to be 
	 * specified, if any.
	 */
	public function fetchRequestToken();
	
	/**
	* Exchange request token and secret for an access token and
	* secret, to sign API calls.
	* The returned object should contain:-
	* 'error'			=> Whether or not an error had occured.
	* 'oauth_key' 		=> The token key assigned.
	* 'oauth_secret'	=> The token secret assigned.
	* 'expires' 		=> The date on which the token is set to expire,
	* if applicable. Zero (0) is to be used where none is set.
	*/
	public function fetchAccessToken($oauth_verifier = FALSE, $reqToken = NULL);
	
	/**
	 * Updates/refreshes the current token based on the procedure
	 * defined by the client network.
	 */
	public function refreshToken();
		
	//TODO Add api method
}