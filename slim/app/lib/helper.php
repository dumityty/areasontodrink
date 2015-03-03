<?php

/**
 * Sets the response type, status code and the body.
 * Function used in all route files to return the JSON response.
 * @param int $status_code [description]
 * @param Object $body        [description]
 */
function setResponse($status_code, $body) {
  $app = \Slim\Slim::getInstance();
  $response = $app->response();
  $response['Content-Type'] = 'application/json';
  $response->status($status_code);
  $response->body(json_encode($body));
}

/**
 * A function to check whether any parameters are missing.
 *
 * It will create a list with all the missing parameters.
 *
 * @param  [type] $request_params  [description]
 * @param  [type] $required_fields [description]
 * @return [type]                  [description]
 */
function checkRequiredParams($request_params, $required_fields) {
  $error = FALSE;
  $error_fields = "";

  foreach ($required_fields as $field) {
      if (!isset($request_params->$field) || strlen(trim($request_params->$field)) <= 0) {
          $error = true;
          $error_fields .= $field . ', ';
      }
  }

  if ($error) {
    throw new Exception('Following fields are missing or empty: ' . substr($error_fields, 0, -2));
  }
}
