<?php
namespace NSV\Misc;

class Kontakt extends \NSV\Core\Page {
  
  private $recipients = array("webmaster@nsv-online.de", "benjamin.loehnhardt@nsv-online.de");
  
  private $hideForm = false;
  private $messageInput;
  private $emailInput;
  
  function __construct() {
    $this->form = new \NSV\Core\Form('kontakt');
    $this->messageInput = $this->form->addTextarea('Nachricht')->required();
    $this->emailInput = $this->form->addEmailInput('Ihre Email-Adresse')->required();
    $this->form->addCaptcha();
    $this->form->addTermsAndConditionsCheckbox();
  }
  
  function preprocess() {
    $this->form->processForm();
    
    if ($this->form->hasValidSubmission()) {
      $subject = 'Anfrage über NSV Kontaktformular von ' . $this->emailInput->getValue();
      $content = $this->messageInput->getValue() . "\n\n(Über NSV-Kontaktformular von " . $this->emailInput->getValue() . ")\n";
      if (wp_mail($this->recipients, $subject, $content)) {
        $this->addSuccessMessage('Anfrage erfolgreich gesendet!');
        $this->hideForm = true;
      } else {
        $this->addErrorMessage('Interner Fehler beim Senden der Anfrage. Bitte wenden Sie sich an webmaster@nsv-online.de');
      }
    }
  }

  function getTitle() {
    return 'Kontaktformular';
  }

  function printPage() {
    if ($this->hideForm) return;
    
    ?>
		<p>
        <ul>
            <li>Bei Fragen zum <b>Ergebnisdienst</b> (z.B. Ergebnisse, Namenskorrekturen) wenden Sie sich bitte immer
            zuerst an den zust&auml;ndigen Staffel- oder Turnierleiter!</li>
            <li><b>Vereinsdaten</b> (z.B. Website oder Spiellokal) werden t&auml;glich aus der Mitgliederverwaltung importiert
            und k&ouml;nnen nur vom Verein selbst &uuml;ber das <a href="http://nsv.portal64.de">Online-Portal</a> bearbeitet werden.</li>
        </ul> 
    </p>
    <?php
    
    $this->form->printForm();
  }
}
