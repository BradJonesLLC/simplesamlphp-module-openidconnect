<?php

$config = array(
  'openid-connect' => array(
      'openidconnect:Connect',
      'client_id'           => '*****-*****-*****-*****-*****',
      'client_secret'       => '*****-*****-*****-*****-*****',
      'token_endpoint'      => 'https://openid.example.org/openid/token',          //Example url
      'user_info_endpoint'  => 'https://openid.example.org/openid/userinfo',      //Example url
      'auth_endpoint'       => 'https://openid.example.org/openid/authorization',  //Example url
      'sslcapath'           => '/etc/ssl/certs',
  ),

);
