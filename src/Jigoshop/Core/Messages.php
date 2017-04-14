<?php

namespace Jigoshop\Core;

use Jigoshop\Entity\Session;
use Jigoshop\Service\SessionServiceInterface;
use WPAL\Wordpress;

/**
 * Class containing Jigoshop messages.
 *
 * @package Jigoshop\Core
 * @author  Amadeusz Starzykiewicz
 */
class Messages
{
	const NOTICES = 'jigoshop_notices';
	const WARNINGS = 'jigoshop_warnings';
	const ERRORS = 'jigoshop_errors';

	private $notices = [];
	private $warnings = [];
	private $errors = [];
    /** @var  Session  */
    private $session;

	public function __construct(Wordpress $wp, SessionServiceInterface $sessionService)
	{
	    $this->session = $sessionService->get($sessionService->getCurrentKey());
		if ($this->session->getField(self::NOTICES)) {
			$this->notices = $this->session->getField(self::NOTICES);
		}
		if ($this->session->getField(self::WARNINGS)) {
            $this->warnings = $this->session->getField(self::WARNINGS);
		}
		if ($this->session->getField(self::ERRORS)) {
            $this->errors = $this->session->getField(self::ERRORS);
		}

		$wp->addAction('shutdown', [$this, 'preserveMessages'], 9);
	}

	/**
	 * @param $message    string Notice message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addNotice($message, $persistent = true)
	{
		$this->notices[] = [
			'message' => $message,
			'persistent' => $persistent,
        ];
	}

	/**
	 * @return bool Whether there are notices to show.
	 */
	public function hasNotices()
	{
		return !empty($this->notices);
	}

	/**
	 * @return array Stored notices.
	 */
	public function getNotices()
	{
		$notices = array_map(function ($item){
			return $item['message'];
		}, $this->notices);
		$this->notices = [];

		return $notices;
	}

	/**
	 * @param $message    string Warning message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addWarning($message, $persistent = true)
	{
		$this->warnings[] = [
			'message' => $message,
			'persistent' => $persistent,
        ];
	}

	/**
	 * @return bool Whether there are warnings to show.
	 */
	public function hasWarnings()
	{
		return !empty($this->warnings);
	}

	/**
	 * @return array Stored warnings.
	 */
	public function getWarnings()
	{
		$warnings = array_map(function ($item){
			return $item['message'];
		}, $this->warnings);
		$this->warnings = [];

		return $warnings;
	}

	/**
	 * @param $message    string Error message.
	 * @param $persistent bool Is this message persistent (available after redirect)?
	 */
	public function addError($message, $persistent = true)
	{
		$this->errors[] = [
			'message' => $message,
			'persistent' => $persistent,
        ];
	}

	/**
	 * @return bool Whether there are errors to show.
	 */
	public function hasErrors()
	{
		return !empty($this->errors);
	}

	/**
	 * @return array Stored errors.
	 */
	public function getErrors()
	{
		$errors = array_map(function ($item){
			return $item['message'];
		}, $this->errors);
		$this->errors = [];

		return $errors;
	}

	/**
	 * Preserves messages storing them to PHP session.
	 */
	public function preserveMessages()
	{
        $this->session->setField(self::NOTICES, array_values(array_filter($this->notices, function ($item){
			return $item['persistent'];
		})));
        $this->session->setField(self::WARNINGS, array_values(array_filter($this->warnings, function ($item){
			return $item['persistent'];
		})));
		$this->session->setField(self::ERRORS, array_values(array_filter($this->errors, function ($item){
			return $item['persistent'];
		})));
	}

	/**
	 * Removes all stored messages.
	 */
	public function clear()
	{
		$this->notices = [];
		$this->warnings = [];
		$this->errors = [];
		$this->preserveMessages();
	}
}
