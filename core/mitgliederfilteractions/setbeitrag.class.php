<?php

require_once(VPANEL_CORE . "/mitgliederfilteraction.class.php");
require_once(VPANEL_PROCESSES . "/mitgliederfilterbeitrag.class.php");

class SetBeitragMitgliederFilterAction extends MitgliederFilterAction {
	private $beitragid;

	public function __construct($actionid, $label, $permission, $beitragid = null) {
		parent::__construct($actionid, $label, $permission);
		$this->beitragid = $beitragid;
	}

	protected function getBeitragID($session) {
		if ($this->beitragid != null) {
			return $this->beitragid;
		}
		if ($session->hasVariable("beitragid")) {
			return $session->getVariable("beitragid");
		}
		return null;
	}

	public function execute($config, $session, $filter, $matcher) {
		$beitragid = $this->getBeitragID($session);
		if ($beitragid == null) {
			$beitraglist = $session->getStorage()->getBeitragList();
			return array("setbeitrag" => "select", "beitraglist" => $beitraglist);
		}

		$process = new MitgliederFilterBeitragProcess($session->getStorage());
		$process->setBeitragID($beitragid);
		return $this->executeProcess($session, $process, $filter, $matcher);
	}

	public function show($config, $session, $process) {
		return array("redirect" => $session->getLink("mitglieder"));
	}
}

?>
