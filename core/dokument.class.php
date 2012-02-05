<?php

require_once(VPANEL_CORE . "/storageobject.class.php");

class Dokument extends StorageClass {
	private $dokumentid;
	private $dokumentkategorieid;
	private $dokumentstatusid;
	private $identifier;
	private $label;
	private $content;
	private $fileid;

	private $dokumentkategorie;
	private $dokumentstatus;
	private $file;

	public static function factory(Storage $storage, $row) {
		$dokument = new Dokument($storage);
		$dokument->setDokumentID($row["dokumentid"]);
		$dokument->setDokumentKategorieID($row["dokumentkategorieid"]);
		$dokument->setDokumentStatusID($row["dokumentstatusid"]);
		$dokument->setIdentifier($row["identifier"]);
		$dokument->setLabel($row["label"]);
		$dokument->setContent($row["content"]);
		$dokument->setFileID($row["fileid"]);
		return $dokument;
	}

	public function getDokumentID() {
		return $this->dokumentid;
	}

	public function setDokumentID($dokumentid) {
		$this->dokumentid = $dokumentid;
	}

	public function getDokumentKategorieID() {
		return $this->dokumentkategorieid;
	}

	public function setDokumentKategorieID($dokumentkategorieid) {
		if ($dokumentkategorieid != $this->dokumentkategorieid) {
			$this->dokumentkategorie = null;
		}
		$this->dokumentkategorieid = $dokumentkategorieid;
	}

	public function getDokumentKategorie() {
		if ($this->dokumentkategorie == null) {
			$this->dokumentkategorie = $this->getStorage()->getDokumentKategorie($this->dokumentkategorieid);
		}
		return $this->dokumentkategorie;
	}

	public function setDokumentKategorie($dokumentkategorie) {
		$this->setDokumentKategorieID($dokumentkategorie->getDokumentKategorieID());
		$this->dokumentkategorie = $dokumentkategorie;
	}

	public function getDokumentStatusID() {
		return $this->dokumentstatusid;
	}

	public function setDokumentStatusID($dokumentstatusid) {
		if ($dokumentstatusid != $this->dokumentstatusid) {
			$this->dokumentstatus = null;
		}
		$this->dokumentstatusid = $dokumentstatusid;
	}

	public function getDokumentStatus() {
		if ($this->dokumentstatus == null) {
			$this->dokumentstatus = $this->getStorage()->getDokumentStatus($this->dokumentstatusid);
		}
		return $this->dokumentstatus;
	}

	public function setDokumentStatus($dokumentstatus) {
		$this->setDokumentStatusID($dokumentstatus->getDokumentStatusID());
		$this->dokumentstatus = $dokumentstatus;
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function getContent() {
		return $this->content;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function getFileID() {
		return $this->fileid;
	}

	public function setFileID($fileid) {
		if ($fileid != $this->fileid) {
			$this->file = null;
		}
		$this->fileid = $fileid;
	}

	public function getFile() {
		if ($this->file == null) {
			$this->file = $this->getStorage()->getFile($this->fileid);
		}
		return $this->file;
	}

	public function setFile($file) {
		$this->setFileID($file->getFileID());
		$this->file = $file;
	}

	public function save(Storage $storage = null) {
		if ($storage === null) {
			$storage = $this->getStorage();
		}
		$this->setDokumentID( $storage->setDokument(
			$this->getDokumentID(),
			$this->getDokumentKategorieID(),
			$this->getDokumentStatusID(),
			$this->getIdentifier(),
			$this->getLabel(),
			$this->getContent(),
			$this->getFileID() ));
	}
}

?>