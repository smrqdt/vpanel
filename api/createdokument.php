<?php

require_once(dirname(__FILE__) . "/../config.inc.php");

$session = $config->getSession(true);
$api = $session->getTemplate();

if (!$session->isSignedIn()) {
	$api->output(array("failed" => "AUTH_MISSING"), 401);
	exit;
}

$dokumenttemplate = $session->getStorage()->getDokumentTemplate($session->getVariable("dokumenttemplateid"));

if ($dokumenttemplate == null) {
	$api->output(array("failed" => "DOKUMENTTEMPLATE_MISSING"), 400);
	exit;
}

$gliederungid = $dokumenttemplate->getDokumentGliederungID($session);
$kategorieid = $dokumenttemplate->getDokumentKategorieID($session);
$statusid = $dokumenttemplate->getDokumentStatusID($session);

$file = $dokumenttemplate->getDokumentFile($session);

if (!$session->isAllowed("dokumente_create", $gliederungid)) {
	$api->output(array("failed" => "PERMISSION_DENIED"), 403);
	exit;
}

if ($file == null) {
	$api->output(array("failed" => "FILE_MISSING"), 400);
} else {
	$dokument = new Dokument($session->getStorage());
	$dokument->setGliederungID($gliederungid);
	$dokument->setDokumentKategorieID($kategorieid);
	$dokument->setDokumentStatusID($statusid);
	$dokument->setIdentifier($dokumenttemplate->getDokumentIdentifier($session));
	$dokument->setLabel($dokumenttemplate->getDokumentLabel($session));
	$dokument->setFile($file);
	$dokument->setData($dokumenttemplate->getDokumentData($session));
	// Zwischenspeichern um die ID zu bekommen
	$dokument->save();

	$notiz = new DokumentNotiz($session->getStorage());
	$notiz->setDokument($dokument);
	$notiz->setAuthor($session->getUser());
	$notiz->setTimestamp(time());
	$notiz->setNextKategorieID($kategorieid);
	$notiz->setNextStatusID($statusid);
	$notiz->setNextLabel($dokumenttemplate->getDokumentLabel($session));
	$notiz->setNextIdentifier($dokumenttemplate->getDokumentIdentifier($session));
	$notiz->setKommentar($dokumenttemplate->getDokumentKommentar($session));

	foreach ($dokumenttemplate->getDokumentFlags($session) as $flagid) {
		$flag = $session->getStorage()->getDokumentFlag($flagid);
		$dokument->setFlag($flag);
		$notiz->setAddFlag($flag);
	}

	$dokument->save();
	$notiz->save();

	$notiz->notify();

	$api->output(array("success" => "1"));
}

?>
