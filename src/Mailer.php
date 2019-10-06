<?php
namespace Knister;
use PHPMailer\PHPMailer\PHPMailer as PHPMailer;

class Mailer  {
	private $smtpServer;
	private $smtpUser;
	private $smtpPassword;
	private $smtpAuth;
	private $smtpSecure;
	private $smtpPort;

	private $defaultConfig = [
		"from" => "sendmail@frischluft-medien.at",
		"fromName" => "Frischluft Medien",
		"subject" => "",
		"htmlBody" => "&nbsp;",
		"textBody" => " ",
		"toAddresses" => [],
		"ccAddresses" => [],
		"bccAddresses" => [],
		"replyTo" => "no-reply@frischluft-medien.at"
	];
	private $permanentConfig = [];

	private $mail;

	public function __construct($smtpServer, $smtpUser, $smtpPassword, $smtpAuth = true, $smtpSecure = "tls", $smtpPort = 587) {
		$this->smtpServer = $smtpServer;
		$this->smtpUser = $smtpUser;
		$this->smtpPassword = $smtpPassword;
		$this->smtpAuth = $smtpAuth;
		$this->smtpSecure = $smtpServer;
		$this->smtpPort = $smtpPort;
	}

	public function setPermanentConfig($permanentConfig = []) {
		$this->permanentConfig = $permanentConfig;
	}

	private function getConfigValue($key) {
		if (array_key_exists($key, $this->permanentConfig)) return $this->permanentConfig[$key];
		if (array_key_exists($key, $this->defaultConfig)) return $this->defaultConfig[$key];
		return "";
	}

	public function sendMail($config = []) {
		$from = array_key_exists("from", $config) ? $config["from"] : $this->getConfigValue("from");
		$fromName = array_key_exists("fromName", $config) ? $config["fromName"] : $this->getConfigValue("fromName");
		$subject = array_key_exists("subject", $config) ? $config["subject"] : $this->getConfigValue("subject");
		$htmlBody = array_key_exists("htmlBody", $config) ? $config["htmlBody"] : $this->getConfigValue("htmlBody");
		$textBody = array_key_exists("textBody", $config) ? $config["textBody"] : $this->getConfigValue("textBody");
		$toAddresses = array_key_exists("toAddresses", $config) ? $config["toAddresses"] : $this->getConfigValue("toAddresses");
		$ccAddresses = array_key_exists("ccAddresses", $config) ? $config["ccAddresses"] : $this->getConfigValue("ccAddresses");
		$bccAddresses = array_key_exists("bccAddresses", $config) ? $config["bccAddresses"] : $this->getConfigValue("bccAddresses");
		$replyTo = array_key_exists("replyTo", $config) ? $config["replyTo"] : $this->getConfigValue("replyTo");

		$this->mail = new PHPMailer;
		$this->mail->isSMTP();
		$this->mail->SMTPAuth = true;
		$this->mail->Host = $this->smtpServer;
		$this->mail->Username = $this->smtpUser;
		$this->mail->Password = $this->smtpPassword;
		$this->mail->SMTPSecure = $this->smtpSecure;
		$this->mail->Port = $this->smtpPort;

		$this->mail->SMTPOptions = array (
			'ssl' => array (
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$this->mail->setFrom($from, $fromName);
		$this->mail->CharSet = 'utf-8';

		$this->mail->addReplyTo($replyTo);

		if ($htmlBody!="") $this->mail->isHTML(true);

		$this->mail->Subject = $subject;
		$this->mail->Body = $htmlBody;
		$this->mail->AltBody = $textBody;

		//add recipients
		foreach ($toAddresses as $toAddress) {
			if (is_string($toAddress)) $this->mail->addAddress($toAddress);
			elseif (is_array($toAddress)) isset($toAddress[1]) ? $this->mail->addAddress($toAddress[0], $toAddress[1]) : $this->mail->addAddress($toAddress[0]);
		}
		foreach ($ccAddresses as $ccAddress) {
			if (is_string($ccAddress)) $this->mail->addCC($ccAddress);
			elseif (is_array($ccAddress)) isset($ccAddress[1]) ? $this->mail->addCC($ccAddress[0], $ccAddress[1]) : $this->mail->addCC($ccAddress[0]);
		}
		foreach ($bccAddresses as $bccAddress) {
			if (is_string($bccAddress)) $this->mail->addBCC($bccAddress);
			elseif (is_array($bccAddress)) isset($bccAddress[1]) ? $this->mail->addBCC($bccAddress[0], $bccAddress[1]) : $this->mail->addBCC($bccAddress[0]);
		}

		if (!$this->mail->send()) {
			$feedback["status"] = false;
			$feedback["msg"] = $this->mail->ErrorInfo;
		} else {
			$feedback["status"] = true;
			$feedback["msg"] = "";
		}

		return $feedback;
	}
}
