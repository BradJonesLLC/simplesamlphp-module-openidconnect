<?php

/*use InoOicClient\Flow\Basic;
use InoOicClient\Oic\Authorization;*/
use Zend\Http\Request;

include('OAuth2.php');
// This class is not namespaced as simplesamlphp does not namespace its classes.

class sspmod_dataportenopenidconnect_Auth_Source_Connect extends SimpleSAML_Auth_Source {

  /**
  * The client ID
  */
  protected $clientId;

  /**
  * The client secret.
  */
  protected $clientSecret;

  /**
  * The token endpoint.
  */
  protected $tokenEndpoint;

  /**
  * The user info endpoint.
  */
  protected $userInfoEndpoint;

  /**
  * The auth endpoint.
  */
  protected $authEndpoint;

  /**
  * The sslcapath for the Zend Http client.
  * @see http://framework.zend.com/manual/current/en/modules/zend.http.client.adapters.html
  */
  protected $sslcapath;

  /**
  * The scope we're requesting.
  */
  protected $scope = 'openid profile email';

  /**
  * Constructor for this authentication source.
  *
  * @param array $info  Information about this authentication source.
  * @param array $config  Configuration.
  */
  public function __construct($info, $config) {
    /* Call the parent constructor first, as required by the interface. */
    parent::__construct($info, $config);

    $this->clientId = $config['client_id'];
    $this->clientSecret = $config['client_secret'];
    $this->tokenEndpoint = $config['token_endpoint'];
    $this->userInfoEndpoint = $config['user_info_endpoint'];
    $this->authEndpoint = $config['auth_endpoint'];
    //$this->sslcapath = $config['sslcapath'];
  }

  /**
  * Return the config array.
  */
  protected function getConfig() {
    return array(

      'client_id' => $this->clientId,
      'redirect_uri' => SimpleSAML_Module::getModuleURL('dataportenopenidconnect/resume.php'),
      'authorization_endpoint' => $this->authEndpoint,
      'token_endpoint' => $this->tokenEndpoint,
      'user_info_endpoint' => $this->userInfoEndpoint,
      'response_type' => 'code',
    );
  }

  /**
  * Log in using an external authentication helper.
  *
  * @param array &$state  Information about the current authentication.
  */
  public function authenticate(&$state) {
    $state['dataportenopenidconnect:AuthID'] = $this->authId;
    $stateId = SimpleSAML_Auth_State::saveState($state, 'dataportenopenidconnect:Connect', TRUE);
    //$flow = new Basic($this->getConfig($stateId));
    //$uri = $flow->getAuthorizationRequestUri($this->scope);
    $info = $this->getConfig($stateId);
    \SimpleSAML\Utils\HTTP::redirectTrustedURL($info["authorization_endpoint"], array(
      "client_id"     => $info["client_id"],
      "redirect_uri"  => $info["redirect_uri"],
      "response_type" => "code",
      "scope"         => $this->scope,
      "state"         => $stateId
    ));

  }

  /**
  *
  * Returns the equivalent of Apache's $_SERVER['REQUEST_URI'] variable.
  *
  * Because $_SERVER['REQUEST_URI'] is only available on Apache, we generate an equivalent using other environment variables.
  *
  * Taken from Drupal.
  * @see https://api.drupal.org/api/drupal/includes!bootstrap.inc/function/request_uri/7
  */
  public static function requesturi() {
    if (isset($_SERVER['REQUEST_URI'])) {
      $uri = $_SERVER['REQUEST_URI'];
    }
    else {
      if (isset($_SERVER['argv'])) {
        $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['argv'][0];
      }
      elseif (isset($_SERVER['QUERY_STRING'])) {
        $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
      }
      else {
        $uri = $_SERVER['SCRIPT_NAME'];
      }
    }
    // Prevent multiple slashes to avoid cross site requests via the Form API.
    $uri = '/' . ltrim($uri, '/');

    return $uri;
  }

  /**
  * Map attributes from the response.
  */
  protected static function getAttributes($user) {
    // Map certain values to new keys but then return everything, in case
    // we need raw attributes from the server.
    foreach ($user as &$u) {
      // Wrap all values in an array, as SSP will expect.
      if (!is_array($u)) {
        $u = array($u);
      }
    }
    $mapped = array(
      'uid' => $user['sub'],
      'mail' => $user['email'],
      'picture' => $user['picture'],
    );
    return $mapped + $user;
  }

  /**
  * Fetch config from static object
  *
  * This function fetches and returns the config, similar to the getConfig
  * function, just for static objects.
  *
  * @param sspmod_dataportenopenidconnect_Auth_Source_Connect $that  Static object
  */
  private static function getConfigSelf($that) {
    return array(
      'client_id'               => $that->clientId,
      'client_secret'           => $that->clientSecret,
      'redirect_uri'            => SimpleSAML_Module::getModuleURL('dataportenopenidconnect/resume.php'),
      'authorization_endpoint'  => $that->authEndpoint,
      'token_endpoint'          => $that->tokenEndpoint,
      'user_info_endpoint'      => $that->userInfoEndpoint,
      'response_type'           => 'code',
    );
  }

  /**
  * Resume authentication process.
  *
  * This function resumes the authentication process after the user has
  * entered his or her credentials.
  *
  * @param array &$state  The authentication state.
  */
  public static function resume() {
    $request = Request::fromString($_SERVER['REQUEST_METHOD'] . ' ' . self::requesturi());
    if (!$stateId = $request->getQuery('state')) {
      throw new SimpleSAML_Error_BadRequest('Missing "state" parameter.');
    }
    $state = SimpleSAML_Auth_State::loadState($stateId, 'dataportenopenidconnect:Connect');

    if (!$code = $request->getQuery('code')) {
      throw new SimpleSAML_Error_BadRequest('Missing "code" parameter.');
    }
    /*
    * Now we have the $state-array, and can use it to locate the authentication
    * source.
    */
    $source = SimpleSAML_Auth_Source::getById($state['dataportenopenidconnect:AuthID']);
    if ($source === NULL) {
      /*
      * The only way this should fail is if we remove or rename the authentication source
      * while the user is at the login page.
      */
      throw new SimpleSAML_Error_Exception('Could not find authentication source.');
    }

    /*
    * Make sure that we haven't switched the source type while the
    * user was at the authentication page. This can only happen if we
    * change config/authsources.php while an user is logging in.
    */
    if (! ($source instanceof self)) {
      throw new SimpleSAML_Error_Exception('Authentication source type changed.');
    }
    $info = $source->getConfigSelf($source);
    $oauth2 = new OAuth2([
      "client_id"     => $info["client_id"],
      "client_secret" => $info["client_secret"],
      "redirect_uri"  => $info["redirect_uri"],
      "auth"          => $info["authorization_endpoint"],
      "token"         => $info["token_endpoint"],
    ]);

    $access_token = $oauth2->get_access_token($code);
    $user = $oauth2->get_identity($access_token, $info['user_info_endpoint']);
    if(!array_key_exists('sub', $user)) {
      /*
      * The user isn't authenticated.
      *
      * Here we simply throw an exception, but we could also redirect the user back to the
      * login page.
      */
      throw new SimpleSAML_Error_Exception('User not authenticated after login attempt.', $e->getCode(), $e);
    }

    /*
    * So, we have a valid user. Time to resume the authentication process where we
    * paused it in the authenticate()-function above.
    */

    $state['Attributes'] = self::getAttributes($user);
    SimpleSAML_Auth_Source::completeAuth($state);

    /*
    * The completeAuth-function never returns, so we never get this far.
    */
    assert('FALSE');
  }


  /**
  * This function is called when the user start a logout operation, for example
  * by logging out of a SP that supports single logout.
  *
  * @param array &$state  The logout state array.
  */
  public function logout(&$state) {
    assert('is_array($state)');
    SimpleSAML_Module::callHooks('dataportenopenidconnect_logout', $state);
  }

}
