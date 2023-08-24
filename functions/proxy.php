<?php
/**
 * This file handles the built in proxy that the frontend can use to bypass cors
 *
 * @package fuxt-backend
 */

/*
 * Register custom Proxy API endpoints
 */
function fuxt_proxy_init()
{
    // Setup new endpoint
	register_rest_route("fuxt/v1", "/proxy", [
        [
            "methods" => "POST, GET, DELETE, PATCH, PUT",
            "callback" => "fuxt_proxy_do_request",
            "permission_callback" => "fuxt_proxy_check_permission",
        ],
    ]);

    // Change headers to allow the custom headers we need
    add_filter(
        "rest_pre_serve_request",
        "fuxt_proxy_add_custom_cors_headers",
        20,
        4
    );
}
add_action("rest_api_init", "fuxt_proxy_init");

/*
 * Check's that the request matches allowed Origin and Provider name is in ACF repeater field
 * Returns true || WP_Error
 */
function fuxt_proxy_check_permission($request)
{
    // Security is be on, so check that Origin is on whitelist
    $domain_restricted = get_field("domain_restricted", "option");
    if (
        $domain_restricted &&
        !fuxt_proxy_request_from_allowed_origin($request)
    ) {
        return new WP_Error(
            "permission",
            "Your origin is not allowed to make this Proxy request"
        );
    }

    // Passed Origin checks, now check requested asked for an approved Provider
    $proxy_name = $request->get_header("Fuxt-Proxy-Name");
    $provider = fuxt_proxy_get_provider("name", $proxy_name);

    // Allow if Provider is found
    if ($proxy_name && $provider) {
        return true;
    }

    return new WP_Error("permission", "Your requested Proxy Name is invalid");
}

/*
 * Forwards request to allowed API, adds in Bearer token from ACF Proxy Settings field group
 * Returns WP_REST_Response || WP_Error
 */
function fuxt_proxy_do_request($request)
{
    // Get bearer token from ACF field, include in request below
    $proxy_name = $request->get_header("Fuxt-Proxy-Name");
    $proxy_endpoint = $request->get_header("Fuxt-Proxy-Endpoint");
    $provider = fuxt_proxy_get_provider("name", $proxy_name);

    // Get any found tokens from ACF, build out full request URL
    $auth_header = $provider["authorization_header"] ?? "";
    $url = $provider["base_url"] . $proxy_endpoint;

    // Encode the body to JSON if supplied
    $body = $request->get_json_params();
    if ($body && is_array($body)) {
        $body = json_encode($body);
    }

    // Setup HTTP Request, pass through as much settings as we can
    $args = [
        "headers" => [
            "Authorization" => $auth_header,
            "Content-Type" => $request->get_header("Content-Type"),
        ],
        "method" => $request->get_method(),
        "body" => $body ?? "",
    ];

    // Send remote request! Go Proxy Go!
    $response = wp_remote_request($url, $args);

    // Retrieve information from the $response
    $response_code = wp_remote_retrieve_response_code($response);
    $response_message = wp_remote_retrieve_response_message($response);
    $response_headers = wp_remote_retrieve_headers($response)->getAll();
    $response_body = wp_remote_retrieve_body($response);
    
    // Check if response is JSON, if so then decode it so that when we send it later it's not double encoded
    if( fuxt_proxy_is_json($response_body) ) {
		$response_body = json_decode($response_body);
    }

    // Return data or error back to frontend
    if (!is_wp_error($response)) {
        $response = new WP_REST_Response($response_body, $response_code);

        // Add orginal response's headers 
        foreach($response_headers as $key => $val) {
            $response->set_headers( array($key => $val) );
        }

        // Make sure Flywheel doesn't cache this
        $response->set_headers( array("Cache-Control" => "no-cache, no-store, must-revalidate, max-age=0") );
        return $response;
    }

    return new WP_Error($response_code, $response_message, $response_body);
}

/*
 * This customizes the CORS headers the server will accept, allowing use of our Proxy custom headers
 * Returns true || false
 */
function fuxt_proxy_add_custom_cors_headers($served, $result, $request, $server)
{
    // Abort if not a request to the Proxy endpoint
    if ($request->get_route() !== "/fuxt/v1/proxy") {
        return $served;
    }

    // Now add our headers
    header(
        "Access-Control-Allow-Headers: Fuxt-Proxy-Name, Fuxt-Proxy-Endpoint, Content-Type, Authorization"
    );

    return $served;
}

/*
 * Check that the request is from an allowed Origin.
 * Returns true || false
 */
function fuxt_proxy_request_from_allowed_origin($request)
{
    $origin = get_http_origin();

    // Start with site url as allowed origin.
    $allowed_origins = [site_url(), home_url()];

    // Add fuxt home url to allowed origin.
    $fuxt_home_url = get_option("fuxt_home_url");
    if ($fuxt_home_url) {
        $allowed_origins[] = $fuxt_home_url;
    }

    $allowed_origins = apply_filters("fuxt_allowed_origins", $allowed_origins);

    // Current request comes from an Origin that is allowed
    if (in_array($origin, $allowed_origins, true)) {
        return true;
    }

    return false;
}

/*
 * Return the Provider if found, or false.
 * Returns Array || false
 */
function fuxt_proxy_get_provider($search_key, $search_value)
{
    $proxy_providers = get_field("providers", "option");

    $columns = array_column($proxy_providers, $search_key);
    $found = array_search($search_value, $columns);

    // Return found Provider
    if (is_int($found)) {
        return $proxy_providers[$found];
    }

    return false;
}

/*
 * Checks if a string is JSON
 * Returns Array || false
 */
function fuxt_proxy_is_json($str) {
    $json = json_decode($str);
    return $json && $str != $json;
}