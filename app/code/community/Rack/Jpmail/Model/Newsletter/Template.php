<?php
class Rack_Jpmail_Model_Newsletter_Template extends Mage_Newsletter_Model_Template
{
    public function getMail()
    {
        $textencode = Mage::getStoreConfig('jpmail/jpmail/text_charset');
        $htmlencode = Mage::getStoreConfig('jpmail/jpmail/html_charset');
        
        if(Mage::getStoreConfig('extsmtp/extsmtp/use_external') == 0 && Mage::getStoreConfig('jpmail/jpmail/use_return_path')) {
            $tr = new Zend_Mail_Transport_Sendmail('-f'.Mage::getStoreConfig('jpmail/jpmail/return_path'));
            Zend_Mail::setDefaultTransport($tr);
        }
        
        if(Mage::getStoreConfig('extsmtp/extsmtp/use_external') == 1) {
            $host = Mage::getStoreConfig('extsmtp/extsmtp/smtp_host');
            $config = array(
                            'auth'     => 'login',
                            'username' => Mage::getStoreConfig('extsmtp/extsmtp/smtp_user'),
                            'password' => Mage::getStoreConfig('extsmtp/extsmtp/smtp_password'),
                            'port'     => Mage::getStoreConfig('extsmtp/extsmtp/smtp_port'),
                      );
            if(Mage::getStoreConfig('extsmtp/extsmtp/smtp_secure') == 1) {
                $config['ssl'] = 'ssl';
            }
            $tr = new Zend_Mail_Transport_Smtp($host, $config);
            Zend_Mail::setDefaultTransport($tr);
        }

        if (is_null($this->_mail)) {
            if($this->isPlain()) {
                $this->_mail = new Zend_Mail($textencode);
            } else {
                $this->_mail = new Zend_Mail($htmlencode);
            }
        } else {
            if($this->isPlain() && ($this->_mail->getCharset() !== $textencode)) {
                $this->_mail = new Zend_Mail($textencode);
                $this->_mail->addBcc($this->bcc);
            } elseif (!$this->isPlain() && ($this->_mail->getCharset() !== $htmlencode)) {
                $this->_mail = new Zend_Mail($htmlencode);
                $this->_mail->addBcc($this->bcc);
            }
        }
        
        if(Mage::getStoreConfig('jpmail/jpmail/use_return_path')) {
            $this->_mail->setReturnPath(Mage::getStoreConfig('jpmail/jpmail/return_path'));
        }

        if(Mage::getStoreConfig('jpmail/jpmail/use_reply_to')) {
                $this->_mail->setReplyTo(Mage::getStoreConfig('jpmail/jpmail/reply_to'));
        }

        return $this->_mail;
    }

    /**
     * Send mail to subscriber
     *
     * @param   Mage_Newsletter_Model_Subscriber|string   $subscriber   subscriber Model or E-mail
     * @param   array                                     $variables    template variables
     * @param   string|null                               $name         receiver name (if subscriber model not specified)
     * @param   Mage_Newsletter_Model_Queue|null          $queue        queue model, used for problems reporting.
     * @return boolean
     **/
    public function send($subscriber, array $variables = array(), $name=null, Mage_Newsletter_Model_Queue $queue=null)
    {
        if (!$this->isValidForSend()) {
            return false;
        }

        $textencode = Mage::getStoreConfig('jpmail/jpmail/textcharset');
        $htmlencode = Mage::getStoreConfig('jpmail/jpmail/htmlcharset');

        $email = '';
        if ($subscriber instanceof Mage_Newsletter_Model_Subscriber) {
            $email = $subscriber->getSubscriberEmail();
            if (is_null($name) && ($subscriber->hasCustomerFirstname() || $subscriber->hasCustomerLastname()) ) {
                $name = $subscriber->getCustomerFirstname() . ' ' . $subscriber->getCustomerLastname();
            }
        }
        else {
            $email = (string) $subscriber;
        }

        if (Mage::getStoreConfigFlag(Mage_Newsletter_Model_Subscriber::XML_PATH_SENDING_SET_RETURN_PATH)) {
            $this->getMail()->setReturnPath($this->getTemplateSenderEmail());
        }

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();
        if($this->isPlain()) {
            $name = mb_convert_encoding($name, $textencode, 'utf-8');
            mb_internal_encoding($textencode);
            $mail->addTo($email, mb_encode_mimeheader($name, $textencode));
            mb_internal_encoding('utf-8');
        } else {
            $name = mb_convert_encoding($name, $htmlencode, 'utf-8');
            mb_internal_encoding($htmlencode);
            $mail->addTo($email, mb_encode_mimeheader($name, $htmlencode));
            mb_internal_encoding('utf-8');
        }

        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText(mb_convert_encoding($text, $textencode, 'utf-8'));
            $mail->setSubject(mb_convert_encoding($this->getProcessedTemplateSubject($variables), $textencode, 'utf-8'));
            
            $senderName = mb_convert_encoding($$this->getTemplateSenderName(), $textencode, 'utf-8');
            mb_internal_encoding($textencode);
            $mail->setFrom($this->getSenderEmail(), mb_encode_mimeheader($senderName, $textencode));
            mb_internal_encoding('utf-8');
        }
        else {
            $mail->setBodyHTML(mb_convert_encoding($text, $htmlencode, 'utf-8'));
            $mail->setSubject(mb_convert_encoding($this->getProcessedTemplateSubject($variables), $htmlencode, 'utf-8'));
            
            $senderName = mb_convert_encoding($$this->getTemplateSenderName(), $htmlencode, 'utf-8');
            mb_internal_encoding($htmlencode);
            $mail->setFrom($this->getSenderEmail(), mb_encode_mimeheader($senderName, $htmlencode));
            mb_internal_encoding('utf-8');
            
        }

        try {
            $mail->send();
            $this->_mail = null;
            if (!is_null($queue)) {
                $subscriber->received($queue);
            }
        }
        catch (Exception $e) {
            if ($subscriber instanceof Mage_Newsletter_Model_Subscriber) {
                // If letter sent for subscriber, we create a problem report entry
                $problem = Mage::getModel('newsletter/problem');
                $problem->addSubscriberData($subscriber);
                if (!is_null($queue)) {
                    $problem->addQueueData($queue);
                }
                $problem->addErrorData($e);
                $problem->save();

                if (!is_null($queue)) {
                    $subscriber->received($queue);
                }
            } else {
                // Otherwise throw error to upper level
                throw $e;
            }
            return false;
        }

        return true;
    }
}
