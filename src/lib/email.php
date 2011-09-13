/*
 * Mailer class
 *
 * (c) July 2011 - Goldeneaglecoin
 *
 * Author: Manuel Odendahl - wesen@ruinwesen.com
 */

class Mailer {
  /**
   * Fill out an email template.
   **/
  public function SetEmailParams($template, $data) {
    foreach ($data as $key => $val) {
      $patterns[] = '/#'.$key.'#/';
      $replace[] = $val;
    }
    return preg_replace($patterns, $replace, $template);
  }


  public function FillEmailTemplate($name, $data) {
    $template = $this->GetEmailTemplate($name);
    if (!$template) {
      throw new Exception("Could not find email template named: $name");
    }
    
    $body = stripslashes(html_entity_decode($template['EMAIL_BODY']));
    $mailBody = $this->SetEmailParams($body, $data);
    $mailSub = $this->SetEmailParams($template['EMAIL_SUBJECT'], $data);

    return array($mailBody, $mailSub);
  }
  
  /**
   * Fill out an email template and send it to $to.
   **/
  public function SendEmailTemplate($name, $data, $to) {
    $res = $this->FillEmailTemplate($name, $data);
    $mailBody = $res[0];
    $mailSub = $res[1];
    
    $name = trim($name);

    /** special bcc cases. **/
    switch ($name) {
    case "Newsletter Unsubscribe":
      $bcc = "info@goldeneaglecoin.com";
      break;

    case "Testimonial Thanks":
      $bcc = "info@goldeneaglecoin.com";
      break;
    }

    /** XXX sending emails for invoices has to be handled separately. **/

    require_once("vendor/clsPhpmailer.inc.php");
    $Mail = new PHPMailer();
    $From = $this->admin->GetAdminEmail();

    $Mail->IsSendmail();
    $Mail->From = $From;
    $Mail->FromName = "Golden Eagle Coins";
    $Mail->Priority = 1;
    $Mail->IsHTML(true);
    $Mail->to = array();
    $Mail->AddAddress($to, '');
    $Mail->AddReplyTo('no-reply@goldeneaglecoin.com', '');
    if ($bcc) {
      $Mail->AddBCC($bcc, "Golden Eagle Coins");
    }
    $Mail->Subject = $mailSub;
    $Mail->Body = $mailBody;
    return $Mail->Send();
  }
};