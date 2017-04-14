<?php

namespace Jigoshop\Entity;

class Email implements EntityInterface, \JsonSerializable
{
	/** @var int */
	private $id;
	/** @var string */
	private $title;
	/** @var string */
	private $subject;
	/** @var string */
	private $text;
	/** @var array */
	private $actions = [];
    /** @var array  */
    private $attachments = [];

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return array
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * @param array $actions
	 */
	public function setActions($actions)
	{
		$this->actions = $actions;
	}

	/**
	 * @param string $action
	 */
	public function addAction($action)
	{
		$this->actions[] = $action;
	}

	/**
	 * @param string $action
	 */
	public function removeAction($action)
	{
		$key = array_search($action, $this->actions);
		if ($key !== false) {
			unset($this->actions[$key]);
		}
	}

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param array $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
    }

	public function getStateToSave()
	{
		return [
			'subject' => $this->subject,
			'actions' => $this->actions,
            'attachments' => $this->attachments
        ];
	}

	public function restoreState(array $state)
	{
		if (isset($state['subject'])) {
			$this->subject = $state['subject'];
		}
		if (isset($state['actions'])) {
			$this->actions = $state['actions'];
		}
		if (isset($state['attachments'])) {
		    $this->attachments = $state['attachments'];
        }
	}

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'text' => $this->text,
            'subject' => $this->subject,
            'actions' => $this->actions,
            'attachments' => $this->attachments,
        ];
    }
}
