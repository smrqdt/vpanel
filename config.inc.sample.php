<?php

require_once(dirname(__FILE__) . "/config.default.php");
require_once(VPANEL_UI . "/language.class.php");

class MyConfig extends DefaultConfig {}

$config = new MyConfig;
$config->setStorage(new MySQLStorage("localhost", "root", "anything92", "vpanel"));
$config->registerLang("de", new PHPLanguage(VPANEL_LANGUAGE . "/de.lang.php"));
$config->registerLang("dummy", new EmptyLanguage);

$config->registerPage("index", "index.php");
$config->registerPage("login", "login.php");
$config->registerPage("logout", "login.php?logout");

$config->registerPage("users", "user.php");
$config->registerPage("users_create", "user.php?mode=create");
$config->registerPage("users_details", "user.php?mode=details&userid=%d");
$config->registerPage("users_del", "user.php?mode=delete&userid=%d");
$config->registerPage("users_addrole", "user.php?mode=addrole&userid=%d");
$config->registerPage("users_delrole", "user.php?mode=delrole&userid=%d&roleid=%d");

$config->registerPage("roles", "roles.php");
$config->registerPage("roles_create", "roles.php?mode=create");
$config->registerPage("roles_details", "roles.php?mode=details&roleid=%d");
$config->registerPage("roles_del", "roles.php?mode=delete&roleid=%d");
$config->registerPage("roles_adduser", "user.php?mode=addrole&roleid=%d");
$config->registerPage("roles_deluser", "user.php?mode=delrole&roleid=%d&userid=%d");

$config->registerPage("orte_json", "json/orte.php");

$config->registerPage("mitglieder", "mitglieder.php");
$config->registerPage("mitglieder_ajax", "mitglieder.php?ajax");
$config->registerPage("mitglieder_page", "mitglieder.php?page=%d");
$config->registerPage("mitglieder_create", "mitglieder.php?mode=create&mitgliedschaftid=%d");
$config->registerPage("mitglieder_details", "mitglieder.php?mode=details&mitgliedid=%d");
$config->registerPage("mitglieder_del", "mitglieder.php?mode=delete&mitgliedid=%d");
$config->registerPage("mitglieder_sendmail", "mitglieder.php?mode=sendmail.select&filterid=%s");
$config->registerPage("mitglieder_sendmail.preview", "mitglieder.php?mode=sendmail.preview");
$config->registerPage("mitglieder_sendmail.send", "mitglieder.php?mode=sendmail.send");

$config->registerPage("mailtemplates", "mailtemplates.php");
$config->registerPage("mailtemplates_create", "mailtemplates.php?mode=create");
$config->registerPage("mailtemplates_details", "mailtemplates.php?mode=details&templateid=%d");
$config->registerPage("mailtemplates_del", "mailtemplates.php?mode=delete&templateid=%d");
$config->registerPage("mailattachment", "mailattachment.php?attachmentid=%d");

$config->registerPage("statistik", "statistik.php");

$config->registerPage("processes_view", "processes.php?mode=view&processid=%d");
$config->registerPage("processes_json", "json/processes.php");
#$config->registerPage("");

$config->registerMitgliederFilter(new MitgliederFilter(1, "Ordentliche Mitglieder",
	new AndMitgliederMatcher(
		new NotMitgliederMatcher(new AusgetretenMitgliederMatcher()),
		new MitgliedschaftMitgliederMatcher(1) ) ));
$config->registerMitgliederFilter(new MitgliederFilter(2, "Fördermitglieder",
	new AndMitgliederMatcher(
		new NotMitgliederMatcher(new AusgetretenMitgliederMatcher()),
		new MitgliedschaftMitgliederMatcher(2) ) ));
$config->registerMitgliederFilter(new MitgliederFilter(3, "Ehrenmitglieder",
	new AndMitgliederMatcher(
		new NotMitgliederMatcher(new AusgetretenMitgliederMatcher()),
		new MitgliedschaftMitgliederMatcher(3) ) ));
$config->registerMitgliederFilter(new MitgliederFilter(4, "Natürliche Personen",
	new AndMitgliederMatcher(
		new NotMitgliederMatcher(new AusgetretenMitgliederMatcher()),
		new NatPersonMitgliederMatcher() ) ));
$config->registerMitgliederFilter(new MitgliederFilter(5, "Juristische Personen",
	new AndMitgliederMatcher(
		new NotMitgliederMatcher(new AusgetretenMitgliederMatcher()),
		new JurPersonMitgliederMatcher() ) ));
$config->registerMitgliederFilter(new MitgliederFilter(6, "Momentane Mitglieder",
	new NotMitgliederMatcher(new AusgetretenMitgliederMatcher()) ));
$config->registerMitgliederFilter(new MitgliederFilter(7, "Ausgetretene Mitglieder",
	new AusgetretenMitgliederMatcher() ));

?>
