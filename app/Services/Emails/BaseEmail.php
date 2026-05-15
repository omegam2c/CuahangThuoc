<?php

namespace App\Services\Emails;

abstract class BaseEmail {
    /**
     * Template Method pattern - defines the skeleton of the email sending process
     */
    abstract protected function getSubject(): string;
    abstract protected function getBody(): string;
    abstract protected function getType(): string;
    
    /**
     * Get attachments if any
     */
    protected function getAttachments(): array {
        return [];
    }

    /**
     * Provides data for the mailer service
     */
    public function getEmailData(): array {
        return [
            'subject' => $this->getSubject(),
            'body' => $this->getBody(),
            'type' => $this->getType(),
            'attachments' => $this->getAttachments()
        ];
    }
}
