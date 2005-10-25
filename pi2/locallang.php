<?php
/**
 * Language labels for plugin "tx_terfe_pi2"
 *
 * This file is detected by the translation tool.
 */

$LOCAL_LANG = Array (
	'default' => Array (
		'general_extensionkeys' => 'Extension Keys',
		'general_error' => 'An error occurred!',
		'general_errorcode' => 'Sorry, an error occurred (result code: %s). If the problem persists, please report this error to the webmaster.',
		'general_none' => 'none',

		'views_introduction' => 'Introduction',
		'views_register' => 'Register keys',
		'views_manage' => 'Manage keys',
		'views_admin' => 'Administrate keys',
		
		'registerkeys_title' => 'Register a new extension key',
		'registerkeys_introduction' => 'Please enter a keyname you want to register. It will be validated and checked. If the extension key is not already registered, you\'ll have the chance to do it immediately hereafter.',
		'registerkeys_needlogin' => 'You have to be logged in in order to register extension keys.',
		'registerkeys_extensionkey' => 'Extension key',
		'registerkeys_extensiontitle' => 'Title',
		'registerkeys_extensiontitle_hint' => '(required) name your extension with a title',
		'registerkeys_extensiondescription' => 'Description',
		'registerkeys_extensiondescription_hint' => 'Please make a short and clear statement about what this extension is about',
		'registerkeys_extensionuploadpassword' => 'Upload password',
		'registerkeys_extensionuploadpassword_hint' => 'The upload password is used when you want to update the repository with new versions of your extension. No password = no upload possible',
		'registerkeys_checkvalidity' => 'check validity',
		'registerkeys_doregister' => 'register key',
		'registerkeys_extensionkeynotvalid' => 'Extension key not valid',
		'registerkeys_titlemissing' => 'Please enter a title for your extension key!',
		'registerkeys_success' => 'Registration successful!',
		'registerkeys_success_explanation' => 'You have successfully registered a new extension key.',
		'registerkeys_result_unknown' => 'An unhandled error occurred (result code: %s). Please try again. If the problem persists, please report this code to the webmaster.',
		'registerkeys_result_10500' => 'Sorry, the extension key you have chosen already exists. Just try another one.',
		'registerkeys_result_10502' => 'Sorry, the extension key you have chosen does not follow the format rules for extension keys. Please make sure that your key follows the rules below and try again.',
		'registerkeys_keyisvalid' => 'The key "%s" was not registered, so you can have it ... If you wish to go on, please enter the remaining information into this form:',

		'registerkeys_rules_heading' => 'Extension key format rules',
		'registerkeys_rules_explanation' => 'Please make sure that the extension key you register follows the following rules:',
		'registerkeys_rules_allowedcharacters' => 'Allowed characters are: a-z (lowercase), 0-9 and \'_\' (underscore)', 		
		'registerkeys_rules_prefixes' => 'The key must not being with one of the following prefixes: tx,u,user_,pages,tt_,sys_,ts_language_,csh_',			
		'registerkeys_rules_startandend' => 'Extension keys cannot start or end with 0-9 and \'_\' (underscore)',			
		'registerkeys_rules_length' => 'An extension key must have minimum 3, maximum 30 characters (not counting underscores)',

		'managekeys_needlogin' => 'You have to be logged in in order to manage extension keys.',
		'managekeys_title' => 'Manage your extension keys',
		'managekeys_introduction' => 'Here you can transfer an extension key to a different TYPO3.org user, change your upload password or delete non-used extension keys. Please note that you can only delete a key if you never uploaded a version to the repository which uses that key.',
		'managekeys_transfer' => 'Transfer to user',
		'managekeys_changepassword' => 'Change password',
		'managekeys_delete' => 'Delete',
		'managekeys_deleteareyousure' => 'Are you sure you want to delete the extension key "%s" ?',
		'managekeys_action_transferkey' => 'Transfer key to other user',
		'managekeys_action_transferkey_success' => 'Your key "%s" has been successfully transferred to user "%s".',
		'managekeys_action_transferkey_usernotfound'=> 'Your key "%s" could not be transferred because user "%s" does not exist!', 
		'managekeys_action_deletekey' => 'Delete this key',
		'managekeys_action_deletekey_success' => 'The extension key "%s" has been successfully deleted.',
		'managekeys_action_changeuploadpassword' => 'Change upload password',
		'managekeys_action_changeuploadpassword_success' => 'The upload password of your key "%s" has been updated.',
		'managekeys_uploads' => 'Upl.',
		
		'adminkeys_needlogin' => 'You have to be logged in in order to administrate extension keys.',
		'adminkeys_needadminrights' => 'You need administrative rights in in order to administrate extension keys.',
		
		'introduction_needlogin' => 'Please note: You need to be logged in in order to register or manage extension keys.',
		'introduction_explanation' => '
			<p>An extension key is a string which uniquely identifies your extension worldwide. Having a unique extension key ensures that you can name modules, plugins, PHP-classes, database tables and fields with a prefix that others do not use. It garantees global portability and compatibility. Registration is free and encouraged by the TYPO3 community for all extensions you make.</p>
			<br />
			<p><strong>Good keys</strong> are those which reflect what the extension is about. Examples:</p>
			<br />
			<ul>
				<li>A message board named "Michaels Super Board". Example key: "mc_superboard"</li>
			    <li>A booking system called "Hotel Manager" for hotels. Example key: "hotelmgr"</li>
			    <li>A plugin (poll system) in a series of plugins made by you or your company which is named "Direct People Technology". Example key: "dpt_pollsystem"</li>
			    <li>A skin for TYPO3 with aliens in the background image, named "Black is Back". Example key: "skinb2b"</li>
			</ul>
			<br />
			<p><strong>Notice:</strong> Using "_" (underscores) in your keys is discouraged since it will make the namespace more complex for you to manage. If possible, please avoid underscores!</p>
			<br />
			<p><strong>Bad keys</strong> are strings which convey no information. Examples:</p>
			<br />
			<ul>
				<li>"asdf" - the typical default "whatever"-string. If you want to test the Extension Repository, please use at least a key like "test_asdf"...</li>
			    <li>"d_d_o" - is too much of an abbreviation to tells us anything.</li>
			    <li>"my_super_module_for_typo3" - this begs the question how it can be anything near "super" when you couldn\'t come up with a good extension key...</li>
			    <li>"i_always_use_underscores" - Is bad because it uses underscores (see notice above). For your own sake.</li>
			    <li>"ilove_very_long_extensionskeys" - You will love long extension keys only until you see all your classnames, tables, fields etc. prefixed with it. Keep them SHORT!</li>
			    <li>"iLoveUpperCASE" - Uppercase is NOT allowed.</li>
			</ul>
			<br />
			<p>Some of these "bad examples" are allowed but they don\'t communicate anything useful for the extension.</p>
			<h4>Guidelines for good keys:</h4>
			<br />
			<ol>
				<li>It should make sense.</li>
			  	<li>It should not have to be changed. When the extension key has been picked, it\'s not so easy to change it.</li>
			   	<li>Avoid underscores if you can (stick to a-z0-9) - that will provide you with the least confusing naming of your modules, tables, classes.</li>
			   	<li>Keep it short, less than 10 characters.</li>
			   	<li>All in lowercase.</li>
			   	<li>Although the primary purpose of the an extension key is to be unique rather than convey information, you might look up which keys others has registered for which kind of extensions - that might help you settle for a good key!</li>
			   	<li>Want to test this? Just enter any string prefixed "test_"...</li>
			</ol>			
			<br />
			<p>Anyways, all technical limitations are validated when you submit a string, so just go ahead now...</p>
			<h4>Terms of use:</h4>
			<p>By registering an extension key you accept that all content uploaded to TER (TYPO3 Extension Repository) matches these terms:</p>
			<br />
			<ul>
				<li>Published under the GPL license or GPL compatible</li>
				<li>You hold the copyright of the code or do not infringe the rights of others (meaning that work from others must be under GPL or GPL compatible already!)</li>
			</ul>
			<br />
			<p>Any extensions found to break these terms will be removed without further notice by the webmaster team.</p>
			<p>The webmasters of TYPO3.org refuse to accept any responsibility for the content of extensions found in the repository since that responsibility is on the owner of the associated extension key who is in control of the uploaded content.</p>
		'		
	),
	'dk' => Array (
	),
	'de' => Array (
	),
	'no' => Array (
	),
	'it' => Array (
	),
	'fr' => Array (
	),
	'es' => Array (
	),
	'nl' => Array (
	),
	'cz' => Array (
	),
	'pl' => Array (
	),
	'si' => Array (
	),
	'fi' => Array (
	),
	'tr' => Array (
	),
	'se' => Array (
	),
	'pt' => Array (
	),
	'ru' => Array (
	),
	'ro' => Array (
	),
	'ch' => Array (
	),
	'sk' => Array (
	),
	'lt' => Array (
	),
	'is' => Array (
	),
	'hr' => Array (
	),
	'hu' => Array (
	),
	'gl' => Array (
	),
	'th' => Array (
	),
	'gr' => Array (
	),
	'hk' => Array (
	),
	'eu' => Array (
	),
	'bg' => Array (
	),
	'br' => Array (
	),
	'et' => Array (
	),
	'ar' => Array (
	),
	'he' => Array (
	),
	'ua' => Array (
	),
	'lv' => Array (
	),
	'jp' => Array (
	),
	'vn' => Array (
	),
	'ca' => Array (
	),
	'ba' => Array (
	),
	'kr' => Array (
	),
	'eo' => Array (
	),
	'my' => Array (
	),
);
?>