<?php

namespace Elearning\CompaniesBundle\Services;

use Elearning\CompaniesBundle\Entity\Group;
use Elearning\CompaniesBundle\Entity\Employee;

class MessageService
{
    private $em;
    private $mailer;
    private $default_from_email;

    public function __construct($em, $mailer, $default_from_email)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->default_from_email = $default_from_email;
    }

    public function sendEmail ($destinations, $message)
    {
        $success = false;

        if ($destinations instanceof Employee) {
            $this->sendEmailMessage($destinations, $message);
            $success = true;
        }

        if ($destinations instanceof Group) {
            $emplyees = $destinations->getEmployees();
            if ($emplyees) {
                foreach ($emplyees as $emplyee) {
                    $this->sendEmailMessage($emplyee, $message);
                }
            }
            $success = true;
        }

        return $success;         
    }

    private function sendEmailMessage (Employee $employee, $message)
    {
        $success = false;
        
        $email = $employee->getUser()->getEmail();

        if (\Swift_Validate::email($email)) {

            $msg = \Swift_Message::newInstance()
                ->setSubject($message['subject'])
                ->setFrom(array($this->default_from_email['address'] => $this->default_from_email['sender_name']))
                ->setTo($email)
                ->setBody($message['text'], 'text/html');

            $this->mailer->send($msg);
            $success = true;
        }

        return $success;
        
    }

}
