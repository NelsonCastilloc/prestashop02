<?php

use CrazyElements\PrestaHelper;

$GetAlldisplayHooks = array(
	array(
		'id'   => 'displayTop',
		'name' => 'displayTop',
	),
	array(
		'id'   => 'displayTopColumn',
		'name' => 'displayTopColumn (Not available in all themes)',
	),
	array(
		'id'   => 'displayHome',
		'name' => 'displayHome',
	),
	array(
		'id'   => 'displayNavFullWidth',
		'name' => 'displayNavFullWidth',
	),
	array(
		'id'   => 'displayFooterBefore',
		'name' => 'displayFooterBefore',
	),
	array(
		'id'   => 'displayFooter',
		'name' => 'displayFooter',
	)
);

$custom_hooks = PrestaHelper::get_option( 'crazy_custom_hooks' );
$custom_hooks = Tools::jsonDecode( $custom_hooks, true );
if(isset($custom_hooks)){
	$temparr = array();
	foreach($custom_hooks as $custom_hook => $mod_route){
		$temparr[] = array(
			'id' => $custom_hook,
			'name' => $custom_hook
		); 
	}
	$GetAlldisplayHooks = array_merge($GetAlldisplayHooks,$temparr);
}

$temparr = array();
$extended_mods = \Hook::exec( 'actionCrazyAddHooks', $GetAlldisplayHooks , null, true );

foreach($extended_mods as $extended_hooks){
	foreach($extended_hooks as $extended_hook){
		$temparr[] = array(
			'id' => $extended_hook,
			'name' => $extended_hook
		);
	}
	 	
}
$GetAlldisplayHooks = array_merge($GetAlldisplayHooks,$temparr);