<?php
/**
 * DokuWiki Plugin smtp (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_smtp extends DokuWiki_Plugin {

    /**
     * Return a string usable as EHLO message
     *
     * @param string $ehlo configured EHLO (ovverrides automatic detection)
     * @return string
     */
    static public function getEHLO($ehlo='') {
        if(empty($ehlo)) {
            $ehlo = !empty($_SERVER["SERVER_ADDR"]) ? "[" . $_SERVER["SERVER_ADDR"] . "]" : "localhost.localdomain";
        }
        return $ehlo;
    }

    public function getMethods() {
      $result = array();
      // getEHLO()
      $result[] = array(
        'name' => 'getEHLO',
        'desc' => 'Return a string usable as EHLO message',
        'params' => array( 'ehlo' => 'string'),
        'return' => array( 'ehlo' => 'string'),
      );
      // sendMail()
      $result[] = array(
        'name' => 'sendMail',
        'desc' => 'Send mail using given input',
        'params' => array(
          'info' => array(
            'to' => 'string',
            'cc'=>'string',
            'bcc'=>'string',
            'link'=>'stirng'),
          'type' => 'string'),
        'return' => array( 'result' => 'boolean'),
      );

      return $result;
    }

    /**
     * Send Mail using given input
     *
     * @param array $info detailed informaiton for the email
     * @param string $type type of email template to use
     *
     * @return boolean
     */
     public function sendMail($info, $type) {

       if(!$this->checkType($type)) return false;

       // create an object to send the mail
       $mail = new Mailer();

       if($info['to']) $mail->to($info['to']);
       if($info['cc']) $mail->cc($info['cc']);
       if($info['bcc']) $mail->bcc($info['bcc']);

       // apply template
       $mail->subject($this->setMailSubject($type));
       $mail->setBody($this->setMailBody($info['link'], $type));

       // send the mail
       $ok = $mail->send();

       // check result
       if($ok){
           msg('Message was sent. SMTP seems to work.',1);
           return true;
       }else{
           msg('Message wasn\'t sent. SMTP seems not to work properly.',-1);
           return false;
       }
     }

     private function checkType($type) {
       return $type === 'verification' || $type === 'setPassword';
     }

     private function setMailSubject($type) {
       if($type === 'verification') {
         return 'Paperclip Verification';
       }

       return 'Paperclip Reset Password';
     }

     private function setMailBody($link, $type) {
       if($type === 'verification') {
         return "Hi @USER@\n\nPlease use following link to verify your account:" . $link ;
       }

       return "Hi @USER@\n\nPlease use following link to reset your password:". $link;
     }

}

// vim:ts=4:sw=4:et:
