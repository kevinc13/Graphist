<?php

/**
 * ------------------------------------------
 * Graphist Social Network (Web Application)
 * Coypright (c) 2013
 * ------------------------------------------
 * @author Kevin Chen, Alex Brufsky
 * @version 1.0
 * ------------------------------------------
 */

/*
 | ------------------------------------------
 | Include the core foundation
 | ------------------------------------------
 */

chdir("../");
require_once "Graphist/core.php";

// $client_id = "SDqG1iaVO5rrjXRF8L90ZrfOKPXyelIA";
// $client_secret = "637d39147a4ca0e23dc2cb98edbbe391837752fe036635eb5f285dc75d3b2cf5";

// function stringifyParameters(array $params = array())
// {
//     ksort($params);

//     $stringifiedParams = "";

//     foreach ($params as $key => $value) {
//         if (is_array($value)) {
//             for ($i = 0; $i < count($value); $i++) {
//                 $stringifiedParams .= "{$key}[]={$value[$i]}&";
//             }
//         } else {
//             $stringifiedParams .= "{$key}={$value}&";
//         }
//     }

//     return rawurlencode(substr($stringifiedParams, 0, -1));
// }

// $params = array("uid" => 1);

// $stringToSign = "GET\n/snips/home_stream\n" . stringifyParameters($params);
// print $stringToSign . "<br>";

// $signature = hash_hmac("sha256", $stringToSign, $client_secret);

// print base64_encode($client_id . ":" . $signature);

/*
 | ------------------------------------------
 | Launch Graphist
 | ------------------------------------------
 */

Application::run();