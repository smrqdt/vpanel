<?php

require_once("Smarty/Smarty.class.php");

class Template {
	private $smarty;
	private $session;
	
	public function __construct($session) {
		$this->session = $session;

		$this->smarty = new Smarty;
		$this->smarty->template_dir = dirname(__FILE__) . "/templates";
		$this->smarty->compile_dir = dirname(__FILE__) . "/templates_c";
		$this->smarty->register_modifier("__", array($this, "translate"));
		$this->smarty->register_modifier("___", array($this, "link"));

		$this->smarty->assign("session", $this->session);
		$this->smarty->assign("charset", $this->session->getEncoding());
		$this->smarty->assign("sidebars", array());
	}

	public function translate() {
		$params = func_get_args();
		$string = array_shift($params);

		if ($this->session->getLang()->hasString($string)) {
			$string = iconv($this->session->getLang()->getEncoding(), $this->session->getEncoding(), $this->session->getLang()->getString($string));
		}

		return vsprintf($string, $params);
	}
	public function link($name) {
		$params = func_get_args();
		return call_user_func_array(array($this->session, "getLink"), $params);
	}
	
	private function getStorage() {
		return $this->session->getConfig()->getStorage();
	}
	
	protected function parseUser($user) {
		$row = array();
		$row["userid"] = $user->getUserID();
		$row["username"] = $user->getUsername();
		$row["defaultdokumentkategorieid"] = $user->getDefaultDokumentKategorieID();
		$row["defaultdokumentstatusid"] = $user->getDefaultDokumentStatusID();
		return $row;
	}

	protected function parseUsers($rows) {
		return array_map(array($this, 'parseUser'), $rows);
	}

	protected function parseRole($role) {
		$row = array();
		$row["roleid"] = $role->getRoleID();
		$row["label"] = $role->getLabel();
		$row["description"] = $role->getDescription();
		return $row;
	}

	protected function parseRoles($rows) {
		return array_map(array($this, 'parseRole'), $rows);
	}

	protected function parsePermission($permission) {
		$row = array();
		$row["permissionid"] = $permission->getPermissionID();
		$row["label"] = $permission->getLabel();
		$row["description"] = $permission->getDescription();
		return $row;
	}

	protected function parsePermissions($rows) {
		return array_map(array($this, 'parsePermission'), $rows);
	}

	protected function parseMail($mail) {
		$row = array();
		$row["headers"] = array();
		foreach ($mail->getHeaders() as $headerfield => $headervalue) {
			$row["headers"][$headerfield] = $headervalue;
		}
		$row["body"] = $mail->getBody();
		return $row;
	}

	protected function parseMails($rows) {
		return array_map(array($this, 'parseMail'), $rows);
	}

	protected function parseMailTemplate($template) {
		$row = array();
		$row["templateid"] = $template->getTemplateID();
		$row["label"] = $template->getLabel();
		$row["body"] = $template->getBody();
		$row["headers"] = array();
		foreach ($template->getHeaders() as $header) {
			$row["headers"][$header->getField()] = $header->getValue();
	}
		$row["attachments"] = $this->parseFiles($template->getAttachments());
		return $row;
	}

	protected function parseMailTemplates($rows) {
		return array_map(array($this, 'parseMailTemplate'), $rows);
	}

	protected function parseBeitrag($beitrag) {
		$row = array();
		$row["beitragid"] = $beitrag->getBeitragID();
		$row["label"] = $beitrag->getLabel();
		$row["hoehe"] = $beitrag->getHoehe();
		return $row;
	}

	protected function parseBeitragList($rows) {
		return array_map(array($this, 'parseBeitrag'), $rows);
	}

	protected function parseMitgliedBeitrag($mitgliedbeitrag, &$mitglied = null) {
		$row = array();
		if ($mitglied == null) {
			$mitglied = $this->parseMitglied($mitgliedbeitrag->getMitglied());
		}
		$row["mitglied"] = $mitglied;
		$row["beitrag"] = $this->parseBeitrag($mitgliedbeitrag->getBeitrag());
		$row["hoehe"] = $mitgliedbeitrag->getHoehe();
		$row["bezahlt"] = $mitgliedbeitrag->getBezahlt();
		return $row;
	}

	protected function parseMitgliedBeitragList($rows, &$mitglied = null) {
		return array_map(array($this, 'parseMitgliedBeitrag'), $rows, count($rows) > 0 ? array_fill(0, count($rows), $mitglied) : array());
	}

	protected function parseMitgliederFilter($filter) {
		$row = array();
		$row["filterid"] = $filter->getFilterID();
		$row["label"] = $filter->getLabel();
		return $row;
	}

	protected function parseMitgliederFilters($rows) {
		return array_map(array($this, 'parseMitgliederFilter'), $rows);
	}

	protected function parseMitglied($mitglied) {
		$row = array();
		$row["mitgliedid"] = $mitglied->getMitgliedID();
		$row["globalid"] = $mitglied->getGlobalID();
		$row["eintritt"] = $mitglied->getEintrittsdatum();
		if ($mitglied->isAusgetreten()) {
			$row["austritt"] = $mitglied->getAustrittsdatum();
		}
		$row["beitraege"] = $this->parseMitgliedBeitragList($mitglied->getBeitragList(), $row);
		$row["beitraege_hoehe"] = 0;
		$row["beitraege_bezahlt"] = 0;
		foreach ($mitglied->getBeitragList() as $beitrag) {
			$row["beitraege_hoehe"] += $beitrag->getHoehe();
			$row["beitraege_bezahlt"] += $beitrag->getBezahlt();
		}
		$row["latest"] = $this->parseMitgliedRevision($mitglied->getLatestRevision());
		return $row;
	}

	protected function parseMitglieder($rows) {
		return array_map(array($this, 'parseMitglied'), $rows);
	}

	protected function parseMitgliederFlag($flag) {
		$row = array();
		$row["flagid"] = $flag->getFlagID();
		$row["label"] = $flag->getLabel();
		return $row;
	}

	protected function parseMitgliederFlags($rows) {
		return array_map(array($this, 'parseMitgliederFlag'), $rows);
	}

	protected function parseMitgliederTextField($textfield) {
		$row = array();
		$row["textfieldid"] = $textfield->getTextFieldID();
		$row["label"] = $textfield->getLabel();
		return $row;
	}

	protected function parseMitgliederTextFields($rows) {
		return array_map(array($this, 'parseMitgliederTextField'), $rows);
	}

	protected function parseMitgliederRevisionTextField($revisiontextfield) {
		$row = array();
		$row["textfield"] = $this->parseMitgliederTextField($revisiontextfield->getTextField());
		// Infinite Loop else
		// $row["revision"] = $this->parseMitgliederRevision($revisiontextfield->getRevision());
		$row["value"] = $revisiontextfield->getValue();
		return $row;
	}

	protected function parseMitgliederRevisionTextFields($rows) {
		return array_map(array($this, 'parseMitgliederRevisionTextField'), $rows);
	}

	protected function parseMitgliedNotiz($notiz) {
		$row = array();
		$row["mitgliednotizid"] = $notiz->getMitgliedNotizID();
		$row["mitgliedid"] = $notiz->getMitgliedID();
		if ($notiz->getAuthor() != null) {
			$row["author"] = $this->parseUser($notiz->getAuthor());
		}
		$row["timestamp"] = $notiz->getTimestamp();
		$row["kommentar"] = $notiz->getKommentar();
		return $row;
	}

	protected function parseMitgliedNotizen($rows) {
		return array_map(array($this, 'parseMitgliedNotiz'), $rows);
	}

	protected function parseMitgliedRevision($revision) {
		$row = array();
		$row["revisionid"] = $revision->getRevisionID();
		$row["globalid"] = $revision->getGlobalID();
		$row["timestamp"] = $revision->getTimestamp();
		if ($revision->getUser() != null) {
			$row["user"] = $this->parseUser($revision->getUser());
		}
		$row["mitgliedschaft"] = $this->parseMitgliedschaft($revision->getMitgliedschaft());
		if ($revision->getNatPersonID() != null) {
			$row["bezeichnung"] = $revision->getNatPerson()->getVorname() . " " . $revision->getNatPerson()->getName();
			$row["natperson"] = $this->parseNatPerson($revision->getNatPerson());
		}
		if ($revision->getJurPersonID() != null) {
			$row["bezeichnung"] = $revision->getJurPerson()->getLabel();
			$row["jurperson"] = $this->parseJurPerson($revision->getJurPerson());
		}
		$row["kontakt"] = $this->parseKontakt($revision->getKontakt());
		$row["beitrag"] = $revision->getBeitrag();
		$row["flags"] = $this->parseMitgliederFlags($revision->getFlags());
		$row["textfields"] = $this->parseMitgliederRevisionTextFields($revision->getTextFields());
		$row["geloescht"] = $revision->isGeloescht();
		return $row;
	}

	protected function parseMitgliedRevisions($rows) {
		return array_map(array($this, 'parseMitgliedRevision'), $rows);
	}

	protected function parseNatPerson($natperson) {
		$row = array();
		$row["natpersonid"] = $natperson->getNatPersonID();
		$row["anrede"] = $natperson->getAnrede();
		$row["vorname"] = $natperson->getVorname();
		$row["name"] = $natperson->getName();
		$row["geburtsdatum"] = $natperson->getGeburtsdatum();
		$row["nationalitaet"] = $natperson->getNationalitaet();
		return $row;
	}

	protected function parseJurPerson($jurperson) {
		$row = array();
		$row["jurpersonid"] = $jurperson->getJurPersonID();
		$row["label"] = $jurperson->getLabel();
		return $row;
	}

	protected function parseKontakt($kontakt) {
		$row = array();
		$row["kontaktid"] = $kontakt->getKontaktID();
		$row["adresszusatz"] = $kontakt->getAdresszusatz();
		$row["strasse"] = $kontakt->getStrasse();
		$row["hausnummer"] = $kontakt->getHausnummer();
		$row["ort"] = $this->parseOrt($kontakt->getOrt());
		$row["telefon"] = $kontakt->getTelefonnummer();
		$row["handy"] = $kontakt->getHandynummer();
		$row["email"] = $kontakt->getEMail();
		return $row;
	}

	protected function parseMitgliedschaft($mitgliedschaft) {
		$row = array();
		$row["mitgliedschaftid"] = $mitgliedschaft->getMitgliedschaftID();
		$row["label"] = $mitgliedschaft->getLabel();
		$row["description"] = $mitgliedschaft->getDescription();
		$row["defaultbeitrag"] = $mitgliedschaft->getDefaultBeitrag();
		$row["defaultmailcreate"] = $mitgliedschaft->getDefaultCreateMail();
		return $row;
	}

	protected function parseMitgliedschaften($rows) {
		return array_map(array($this, 'parseMitgliedschaft'), $rows);
	}

	protected function parseOrt($ort) {
		$row = array();
		$row["ortid"] = $ort->getOrtID();
		$row["label"] = $ort->getLabel();
		$row["plz"] = $ort->getPLZ();
		$row["state"] = $this->parseState($ort->getState());
		return $row;
	}

	protected function parseOrte($rows) {
		return array_map(array($this, 'parseOrt'), $rows);
	}

	protected function parseState($state) {
		$row = array();
		$row["stateid"] = $state->getStateID();
		$row["label"] = $state->getLabel();
		$row["population"] = $state->getPopulation();
		$row["country"] = $this->parseCountry($state->getCountry());
		return $row;
	}

	protected function parseStates($rows) {
		return array_map(array($this, 'parseState'), $rows);
	}

	protected function parseCountry($country) {
		$row = array();
		$row["countryid"] = $country->getCountryID();
		$row["label"] = $country->getLabel();
		return $row;
	}

	protected function parseCountries($rows) {
		return array_map(array($this, 'parseCountry'), $rows);
	}

	protected function parseProcess($process) {
		$row = array();
		$row["processid"] = $process->getProcessID();
		$row["progress"] = $process->getProgress();
		$row["queued"] = $process->getQueued();
		$row["started"] = $process->getStarted();
		$row["finished"] = $process->getFinished();
		$row["iswaiting"] = $process->isWaiting();
		$row["isrunning"] = $process->isRunning();
		$row["isfinished"] = $process->isFinished();
		return $row;
	}

	protected function parseProcesses($rows) {
		return array_map(array($this, 'parseProcess'), $rows);
	}

	protected function parseDokument($dokument) {
		$row = array();
		$row["dokumentid"] = $dokument->getDokumentID();
		$row["dokumentkategorie"] = $this->parseDokumentKategorie($dokument->getDokumentKategorie());
		$row["dokumentstatus"] = $this->parseDokumentStatus($dokument->getDokumentStatus());
		$row["identifier"] = $dokument->getIdentifier();
		$row["label"] = $dokument->getLabel();
		$row["content"] = $dokument->getContent();
		$row["file"] = $this->parseFile($dokument->getFile());
		return $row;
	}

	protected function parseDokumente($rows) {
		return array_map(array($this, 'parseDokument'), $rows);
	}

	protected function parseDokumentKategorie($kategorie) {
		$row = array();
		$row["dokumentkategorieid"] = $kategorie->getDokumentKategorieID();
		$row["label"] = $kategorie->getLabel();
		return $row;
	}

	protected function parseDokumentKategorien($rows) {
		return array_map(array($this, 'parseDokumentKategorie'), $rows);
	}

	protected function parseDokumentStatus($status) {
		$row = array();
		$row["dokumentstatusid"] = $status->getDokumentStatusID();
		$row["label"] = $status->getLabel();
		return $row;
	}

	protected function parseDokumentStatusList($rows) {
		return array_map(array($this, 'parseDokumentStatus'), $rows);
	}

	protected function parseDokumentNotiz($notiz) {
		$row = array();
		$row["dokumentnotizid"] = $notiz->getDokumentNotizID();
		$row["author"] = $this->parseUser($notiz->getAuthor());
		$row["timestamp"] = $notiz->getTimestamp();
		if ($notiz->getNextKategorieID() != null) {
			$row["nextkategorie"] = $this->parseDokumentKategorie($notiz->getNextKategorie());
		}
		if ($notiz->getNextStatusID() != null) {
			$row["nextstatus"] = $this->parseDokumentStatus($notiz->getNextStatus());
		}
		$row["kommentar"] = $notiz->getKommentar();
		return $row;
	}

	protected function parseDokumentNotizen($rows) {
		return array_map(array($this, 'parseDokumentNotiz'), $rows);
	}

	protected function parseFile($file) {
		$row = array();
		$row["fileid"] = $file->getFileID();
		$row["exportfilename"] = $file->getExportFilename();
		$row["mimetype"] = $file->getMimeType();
		return $row;
	}

	protected function parseFiles($rows) {
		return array_map(array($this, 'parseFile'), $rows);
	}


	public function viewIndex() {
		$this->smarty->display("index.html.tpl");
	}

	public function viewLogin($loginfailed = false) {
		$errors = array();
		if ($loginfailed) {
			$errors[] = $this->translate("Login failed");
		}
		$this->smarty->assign("errors", $errors);
		$this->smarty->display("login.html.tpl");
	}

	public function viewUserList($users) {
		$this->smarty->assign("users", $this->parseUsers($users));
		$this->smarty->display("userlist.html.tpl");
	}

	public function viewUserDetails($user, $roles, $dokumentkategorien, $dokumentstatuslist) {
		$this->smarty->assign("user", $this->parseUser($user));
		$this->smarty->assign("userroles", $this->parseRoles($user->getRoles()));
		$this->smarty->assign("dokumentkategorien", $this->parseDokumentKategorien($dokumentkategorien));
		$this->smarty->assign("dokumentstatuslist", $this->parseDokumentStatusList($dokumentstatuslist));
		$this->smarty->assign("roles", $this->parseRoles($roles));
		$this->smarty->display("userdetails.html.tpl");
	}

	public function viewUserCreate($dokumentkategorien, $dokumentstatuslist) {
		$this->smarty->assign("dokumentkategorien", $this->parseDokumentKategorien($dokumentkategorien));
		$this->smarty->assign("dokumentstatuslist", $this->parseDokumentStatusList($dokumentstatuslist));
		$this->smarty->display("usercreate.html.tpl");
	}

	public function viewRoleList($roles) {
		$this->smarty->assign("roles", $this->parseRoles($roles));
		$this->smarty->display("rolelist.html.tpl");
	}

	public function viewRoleDetails($role, $users, $permissions) {
		$this->smarty->assign("role", $this->parseRole($role));
		$this->smarty->assign("roleusers", $this->parseUsers($role->getUsers()));
		$this->smarty->assign("users", $this->parseUsers($users));
		$this->smarty->assign("rolepermissions", $this->parsePermissions($role->getPermissions()));
		$this->smarty->assign("permissions", $this->parsePermissions($permissions));
		$this->smarty->display("roledetails.html.tpl");
	}

	public function viewRoleCreate() {
		$this->smarty->display("rolecreate.html.tpl");
	}

	public function viewBeitragList($beitraege) {
		$this->smarty->assign("beitraege", $this->parseBeitragList($beitraege));
		$this->smarty->display("beitraglist.html.tpl");
	}

	public function viewBeitragCreate() {
		$this->smarty->display("beitragcreate.html.tpl");
	}

	public function viewBeitragDetails($beitrag, $mitgliederbeitraglist, $page, $pagecount) {
		$this->smarty->assign("beitrag", $this->parseBeitrag($beitrag));
		$this->smarty->assign("mitgliederbeitraglist", $this->parseMitgliedBeitragList($mitgliederbeitraglist));
		$this->smarty->assign("mitgliederbeitraglist_page", $page);
		$this->smarty->assign("mitgliederbeitraglist_pagecount", $pagecount);
		$this->smarty->display("beitragdetails.html.tpl");
	}

	public function viewMailTemplateList($mailtemplates) {
		$this->smarty->assign("mailtemplates", $this->parseMailTemplates($mailtemplates));
		$this->smarty->display("mailtemplatelist.html.tpl");
	}

	public function viewMailTemplateDetails($mailtemplate) {
		$this->smarty->assign("mailtemplate", $this->parseMailTemplate($mailtemplate));
		$this->smarty->display("mailtemplatedetails.html.tpl");
	}

	public function viewMailTemplateCreate() {
		$this->smarty->display("mailtemplatecreate.html.tpl");
	}

	public function viewMailTemplateCreateAttachment($mailtemplate) {
		$this->smarty->assign("mailtemplate", $this->parseMailTemplate($mailtemplate));
		$this->smarty->display("mailtemplatecreateattachment.html.tpl");
	}

	public function viewMitgliederList($mitglieder, $mitgliedschaften, $filters, $filter, $page, $pagecount) {
		if ($filter != null) {
			$this->smarty->assign("filter", $this->parseMitgliederFilter($filter));
		}
		$this->smarty->assign("page", $page);
		$this->smarty->assign("pagecount", $pagecount);
		$this->smarty->assign("mitglieder", $this->parseMitglieder($mitglieder));
		$this->smarty->assign("mitgliedschaften", $this->parseMitgliedschaften($mitgliedschaften));
		$this->smarty->assign("filters", $this->parseMitgliederFilters($filters));
		$this->smarty->display("mitgliederlist.html.tpl");
	}

	public function viewMitgliedDetails($mitglied, $revisions, $revision, $notizen, $dokumente, $mitgliedschaften, $states, $mitgliederflags, $mitgliedertextfields, $beitraege) {
		$this->smarty->assign("mitglied", $this->parseMitglied($mitglied));
		$this->smarty->assign("mitgliedrevisions", $this->parseMitgliedRevisions($revisions));
		$this->smarty->assign("mitgliedrevision", $this->parseMitgliedRevision($revision));
		$this->smarty->assign("mitgliednotizen", $this->parseMitgliedNotizen($notizen));
		$this->smarty->assign("dokumente", $this->parseDokumente($dokumente));
		$this->smarty->assign("mitgliedschaften", $this->parseMitgliedschaften($mitgliedschaften));
		$this->smarty->assign("states", $this->parseStates($states));
		$this->smarty->assign("flags", $this->parseMitgliederFlags($mitgliederflags));
		$this->smarty->assign("textfields", $this->parseMitgliederTextFields($mitgliedertextfields));
		$this->smarty->assign("beitraege", $this->parseBeitragList($beitraege));
		$this->smarty->display("mitgliederdetails.html.tpl");
	}

	public function viewMitgliedCreate($mitgliedschaft, $dokument, $data, $mitgliedschaften, $states, $mitgliederflags, $mitgliedertextfields) {
		if ($mitgliedschaft != null) {
			$this->smarty->assign("mitgliedschaft", $this->parseMitgliedschaft($mitgliedschaft));
		}
		if ($dokument != null) {
			$this->smarty->assign("dokument", $this->parseDokument($dokument));
		}
		if ($data != null) {
			$this->smarty->assign("data", $data);
		}
		$this->smarty->assign("mitgliedschaften", $this->parseMitgliedschaften($mitgliedschaften));
		$this->smarty->assign("states", $this->parseStates($states));
		$this->smarty->assign("flags", $this->parseMitgliederFlags($mitgliederflags));
		$this->smarty->assign("textfields", $this->parseMitgliederTextFields($mitgliedertextfields));
		$this->smarty->display("mitgliedercreate.html.tpl");
	}

	public function viewMitgliederSendMailForm($filters, $templates) {
		$this->smarty->assign("filters", $this->parseMitgliederFilters($filters));
		$this->smarty->assign("mailtemplates", $this->parseMailTemplates($templates));
		$this->smarty->display("mitgliedersendmailform.html.tpl");
	}

	public function viewMitgliederSendMailPreview($mail, $filter, $mailtemplate) {
		if ($filter != null) {
			$this->smarty->assign("filterid", $filter->getFilterID());
			$this->smarty->assign("filter", $this->parseMitgliederFilter($filter));
		} else {
			$this->smarty->assign("filterid", null);
		}
		$this->smarty->assign("mail", $this->parseMail($mail));
		$this->smarty->assign("mailtemplate", $this->parseMailTemplate($mailtemplate));
		$this->smarty->display("mitgliedersendmailpreview.html.tpl");
	}

	public function viewMitgliederSendMailSend($filter, $mailtemplate, $process) {
		if ($filter != null) {
			$this->smarty->assign("filter", $this->parseMitgliederFilter($filter));
		}
		$this->smarty->assign("mailtemplate", $this->parseMailTemplate($mailtemplate));
		$this->smarty->assign("process", $this->parseProcess($process));
		$this->smarty->display("mitgliedersendmailsend.html.tpl");
	}

	public function viewMitgliederExportOptions($filters, $predefinedfields) {
		$this->smarty->assign("filters", $this->parseMitgliederFilters($filters));
		$this->smarty->assign("predefinedfields", $predefinedfields);
		$this->smarty->display("mitgliederexportform.html.tpl");
	}

	public function viewMitgliederSetBeitragSelect($filters, $beitraglist) {
		$this->smarty->assign("filters", $this->parseMitgliederFilters($filters));
		$this->smarty->assign("beitraglist", $this->parseBeitragList($beitraglist));
		$this->smarty->display("mitgliedersetbeitragselect.html.tpl");
	}

	public function viewStatistik($mitgliedercount, $mitgliedschaften, $states) {
		$countPerMitgliedschaft = array();
		foreach ($mitgliedschaften as $mitgliedschaft) {
			$m = $this->parseMitgliedschaft($mitgliedschaft);
			$m["count"] = $mitgliedschaft->getMitgliederCount();
			$countPerMitgliedschaft[] = $m;
		}
		
		$countPerState = array();
		foreach ($states as $state) {
			$s = $this->parseState($state);
			$s["count"] = $state->getMitgliederCount();
			$countPerState[] = $s;
		}

		$this->smarty->assign("mitgliedercount", $mitgliedercount);
		$this->smarty->assign("mitgliedercountPerMitgliedschaft", $countPerMitgliedschaft);
		$this->smarty->assign("mitgliedercountPerState", $countPerState);
		$this->smarty->display("statistik.html.tpl");
	}

	public function viewProcess($process) {
		$this->smarty->assign("process", $this->parseProcess($process));
		$this->smarty->display("process.html.tpl");
	}

	public function viewDokumentList($dokumente, $dokumentkategorien, $dokumentkategorie, $dokumentstatuslist, $dokumentstatus, $page, $pagecount) {
		if ($dokumentkategorie != null) {
			$this->smarty->assign("dokumentkategorie", $this->parseDokumentKategorie($dokumentkategorie));
		}
		if ($dokumentstatus != null) {
			$this->smarty->assign("dokumentstatus", $this->parseDokumentStatus($dokumentstatus));
		}
		$this->smarty->assign("page", $page);
		$this->smarty->assign("pagecount", $pagecount);
		$this->smarty->assign("dokumente", $this->parseDokumente($dokumente));
		$this->smarty->assign("dokumentkategorien", $this->parseDokumentKategorien($dokumentkategorien));
		$this->smarty->assign("dokumentstatuslist", $this->parseDokumentStatusList($dokumentstatuslist));
		$this->smarty->display("dokumentlist.html.tpl");
	}

	public function viewDokumentCreate($dokumentkategorien, $dokumentkategorie, $dokumentstatuslist, $dokumentstatus) {
		if ($dokumentkategorie != null) {
			$this->smarty->assign("dokumentkategorie", $this->parseDokumentKategorie($dokumentkategorie));
		}
		if ($dokumentstatus != null) {
			$this->smarty->assign("dokumentstatus", $this->parseDokumentStatus($dokumentstatus));
		}
		$this->smarty->assign("dokumentkategorien", $this->parseDokumentKategorien($dokumentkategorien));
		$this->smarty->assign("dokumentstatuslist", $this->parseDokumentStatusList($dokumentstatuslist));
		$this->smarty->display("dokumentcreate.html.tpl");
	}

	public function viewDokumentDetails($dokument, $dokumentnotizen, $mitglieder, $dokumentkategorien, $dokumentstatuslist) {
		$this->smarty->assign("dokument", $this->parseDokument($dokument));
		$this->smarty->assign("dokumentnotizen", $this->parseDokumentNotizen($dokumentnotizen));
		$this->smarty->assign("mitglieder", $this->parseMitglieder($mitglieder));
		$this->smarty->assign("dokumentkategorien", $this->parseDokumentKategorien($dokumentkategorien));
		$this->smarty->assign("dokumentstatuslist", $this->parseDokumentStatusList($dokumentstatuslist));
		$this->smarty->display("dokumentdetails.html.tpl");
	}

	public function viewMitgliedDokumentForm($mitglied, $dokument) {
		if ($mitglied != null) {
			$this->smarty->assign("mitglied", $this->parseMitglied($mitglied));
		}
		if ($dokument != null) {
			$this->smarty->assign("dokument", $this->parseDokument($dokument));
		}
		$this->smarty->assign("mode", $_REQUEST["mode"]);
		$this->smarty->display("mitglieddokument.html.tpl");
	}

	public function viewFileImagePreview($file, $token) {
		$this->smarty->assign("file", $this->parseFile($file));
		$this->smarty->assign("token", $token);
		$this->smarty->display("fileimagepreview.html.tpl");
	}

	public function viewFilePDFPreview($file, $token, $parts) {
		$this->smarty->assign("file", $this->parseFile($file));
		$this->smarty->assign("token", $token);
		$this->smarty->assign("parts", $parts);
		$this->smarty->display("filepdfpreview.html.tpl");
	}

	public function getRedirectURL() {
		return isset($_REQUEST["redirect"]) ? $_REQUEST["redirect"] : $_SERVER["HTTP_REFERER"];
	}

	public function redirect($url = null) {
		if ($url === null) {
			// TODO $session->getVariable nutzen
			$url = $this->getRedirectURL();
		}
		header('Location: ' . $url);
		echo 'Sie werden weitergeleitet: <a href="'.$url.'">'.$url.'</a>';
	}
}

?>
