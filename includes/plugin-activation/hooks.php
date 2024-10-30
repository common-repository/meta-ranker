<?php

/**
 * Copyright (c) Author <contact@website.com>
 *
 * This source code is licensed under the license
 * included in the root directory of this application.
 */

/**
 * Sync data with server
 */
function metaranker_sync_data_with_server()
{
	
	global $wpdb;

	$sync_status = 0;
	$cls_obj = new MRV_Database();

	$sessions = $cls_obj->get_list();

	$resp = "";
	if ($sessions) {
		$auth_token = MetaRankerRestApi::getAuthToken('meta-ranker');
		set_time_limit(120);
		foreach ($sessions as $key => $session) {

			$send_data = array(
				'wallet' => $session['wallet_address'],
				'balance' => 0,
				'walletType' => $session['wallet_type'],
				'data' => array(

					array(
						'key' => 'listName',
						'value' => $session['list_name'],
					),
					array(
						'key' => 'ipAddress',
						'value' => $session['ip'],
					),
					array(
						'key' => 'userAgent',
						'value' => $session['agent'],
					),
					array(
						'key' => 'ArticleUrl',
						'value' => $session['link'],
					),

				),
			);
			$resp = MetaRankerRestApi::request('/v2/data', 'PUT', $send_data, $auth_token);

		}
		//if ($resp['status'] == 200) {
		$cls_obj->update_list();
		//}
	}

}
add_action('metaranker_sync_data', 'metaranker_sync_data_with_server');

/**
 * Bind the `rest_api_init` hook
 *
 * @see https://developer.wordpress.org/reference/hooks/rest_api_init/
 */
function metaranker_on_restapi_init($server)
{
	MetaRankerRestApi::registerRoutes();
}
add_action('rest_api_init', 'metaranker_on_restapi_init');