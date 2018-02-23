 <?php

class Rack_Jpmail_Model_Email_Template extends Mage_Core_Model_Email_Template {

    public function send($email, $name=null, array $variables = array()) {
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }

        $emails = array_values((array) $email);
        $names = is_array($name) ? $name : (array) $name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $textencode = Mage::getStoreConfig('jpmail/jpmail/text_charset');
        $htmlencode = Mage::getStoreConfig('jpmail/jpmail/html_charset');

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));
        $mail = $this->getMail();
        
        //if ($returnPathEmail !== null) {
        //   $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
        //    Zend_Mail::setDefaultTransport($mailTransport);
        //}

        
        foreach ($emails as $key => $email) {
            if ($this->isPlain()) {
                $names[$key] = mb_convert_encoding($names[$key], $textencode, 'utf-8');
                mb_internal_encoding($textencode);
                $mail->addTo($email, mb_encode_mimeheader($names[$key], $textencode));
                mb_internal_encoding('utf-8');
            } else {
                $names[$key] = mb_convert_encoding($names[$key], $htmlencode, 'utf-8');
                mb_internal_encoding($htmlencode);
                $mail->addTo($email, mb_encode_mimeheader($names[$key], $htmlencode));
                mb_internal_encoding('utf-8');
            }
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);


        if ($this->isPlain()) {
            $mail->setBodyText(mb_convert_encoding($text, $textencode, 'utf-8'));
            $subject = mb_convert_encoding($this->getProcessedTemplateSubject($variables), $textencode, 'utf-8');
            $senderName = mb_convert_encoding($this->getSenderName(), $textencode, 'utf-8');
            mb_internal_encoding($textencode);
            $mail->setFrom($this->getSenderEmail(), mb_encode_mimeheader($senderName, $textencode));
            mb_internal_encoding('utf-8');
        } else {
            $mail->setBodyHTML(mb_convert_encoding($text, $htmlencode, 'utf-8'));
            $subject = mb_convert_encoding($this->getProcessedTemplateSubject($variables), $htmlencode, 'utf-8');
            $senderName = mb_convert_encoding($this->getSenderName(), $htmlencode, 'utf-8');
            mb_internal_encoding($textencode);
            $mail->setFrom($this->getSenderEmail(), mb_encode_mimeheader($senderName, $htmlencode));
            mb_internal_encoding('utf-8');
        }

        $mail->setSubject($subject);

        try {
            $mail->send(); // Zend_Mail warning..
            $this->_mail = null;
        } catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }
        return true;
    }

    public function getMail() {
        $textencode = Mage::getStoreConfig('jpmail/jpmail/text_charset');
        $htmlencode = Mage::getStoreConfig('jpmail/jpmail/html_charset');
        
        $setReturnPath = Mage::getStoreConfig('jpmail/jpmail/use_return_path');
        $returnPathEmail = '';
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = Mage::getStoreConfig('jpmail/jpmail/return_path');
                break;
            default:
                $returnPathEmail = '';
                break;
        }

        if(Mage::getStoreConfig('extsmtp/extsmtp/use_external') == 0 && $returnPathEmail !== '') {
            $tr = new Zend_Mail_Transport_Sendmail('-f'.$returnPathEmail);
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
            if ($this->isPlain()) {
                $this->_mail = new Zend_Mail($textencode);
            } else {
                $this->_mail = new Zend_Mail($htmlencode);
            }
        } else {
            if ($this->isPlain() && ($this->_mail->getCharset() !== $textencode)) {
                $this->_mail = new Zend_Mail($textencode);
                $this->_mail->addBcc($this->bcc);
            } elseif (!$this->isPlain() && ($this->_mail->getCharset() !== $htmlencode)) {
                $this->_mail = new Zend_Mail($htmlencode);
                $this->_mail->addBcc($this->bcc);
            }
        }
        if($returnPathEmail !== '' && !$this->_mail->getReturnPath()) {
            $this->_mail->setReturnPath($returnPathEmail);
        }
        if (Mage::getStoreConfig('jpmail/jpmail/use_reply_to')) {
            $this->setReplyTo(Mage::getStoreConfig('jpmail/jpmail/reply_to'));
        }

        return $this->_mail;
    }

    public function addBcc($bcc) {
        if (is_array($bcc)) {
            foreach ($bcc as $email) {
                $this->getMail()->addBcc($email);
            }
        } elseif ($bcc) {
            $this->getMail()->addBcc($bcc);
        }
        $this->bcc = $bcc;
        return $this;
    }
    
    /**
     * Add Reply-To header
     *
     * @param string $email
     * @return Mage_Core_Model_Email_Template
     */
    public function setReplyTo($email)
    {
        if(is_object($this->_mail) && !$this->_mail->getReplyTo()) {
            $this->_mail->setReplyTo($email);
        }
        return $this;
    }

}
