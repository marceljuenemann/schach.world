<?php
namespace NSV\Core;

/**
 * Helper for outputting and processing HTML forms.
 */
class Form {

  public $formId ;
  public $action = '';
  public $method = 'post';
  public $submitLabel = 'Absenden';

  private $inputs = array();
  private $validSubmission = false;

  function __construct($id) {
    $this->formId = 'nsv-form-' . $id;
  }
  
  function processForm() {
    $data = $this->method == 'post' ? $_POST : $_GET;
    if ($data[$this->formId] === 'nsv-form-submitted') {
      $success = true;
      foreach ($this->inputs as $input) {
        $input->processSubmission($data);
        if (!$input->hasValidSubmission()) {
          $success = false;
        }
      }
      $this->validSubmission = $success;
    }
  }
  
  function hasValidSubmission() {
    return $this->validSubmission;
  }
  
  function printForm() {
    echo '<form action="' . $this->action . '" method="' . $this->method . '">';
    foreach ($this->inputs as $input) {
      $input->printInput();
    }
    echo '<button type="submit" class="btn btn-primary" name="' . $this->formId . '" value="nsv-form-submitted">' . $this->submitLabel . '</button>';
    echo '</form>';
  }
  
  private function addInput($input) {
    $this->inputs[] = $input;
    return $input;
  }

  function addTextInput($label) {
    return $this->addInput(new Input($label))->addAttribute('type', 'text');
  }

  function addEmailInput($label) {
    return $this->addInput(new Input($label))->addAttribute('type', 'email')->addValidator(function($value) {
      return filter_var($value, FILTER_VALIDATE_EMAIL) ? true : 'Bitte geben Sie eine gültige Emailadresse für Antworten an';
    });
  }

  function addTextarea($label) {
    return $this->addInput(new Textarea($label))->addAttribute('rows', '5');
  }

  function addCheckbox($label) {
    return $this->addInput(new Checkbox($label));
  }

  function addTermsAndConditionsCheckbox() {
    return $this->addCheckbox('Ich stimme der <a href="/impressum" target="_blank">Datenschutzerklärung</a> zu')->addValidator(function($value) {
      if (!$value) {
        return 'Zum Absenden müssen Sie der Datenschutzerklärung zustimmen!';
      }
      return true;
    });
  }
  
  function addCaptcha() {
    // Just using a very simple chess question for now :)
    return $this->addTextInput('Wie viele Felder hat ein Schachbrett?')->addValidator(function($value) {
      if ($value != 64) {
        return 'Bitte tragen Sie zum Schutz vor Spam als Antwort "64" in dieses Feld ein.';
      }
      return true;
    });
  }
}

abstract class BaseInput {
  protected $label;
  protected $cssClasses = array('form-control');
  protected $attrs = array();
  protected $validators = array();
  protected $validationResult;
  protected $value;

  function __construct($label) {
    $this->label = $label;
    $this->attrs['id'] = $this->getId();
    $this->attrs['name'] = $this->getId();
  }

  function processSubmission($data) {
    $value = $data[$this->getId()];
    $this->value = $value;
    $this->validationResult = array('success' => true, 'errors' => array());
    foreach ($this->validators as $validator) {
      if (($result = $validator($value)) !== true) {
        $this->validationResult['success'] = false;
        $this->validationResult['errors'][] = $result;
      }
    }
    if ($this->validationResult['success']) {
      $this->cssClasses[] = 'is-valid';
      return true;
    } else {
      $this->cssClasses[] = 'is-invalid';
      return false;
    }
  }
  
  function hasValidSubmission() {
    return $this->validationResult && $this->validationResult['success'];
  }

  function printInput() {
    echo '<div class="form-group">';
    echo '<label for="' . $this->getId() . '">' . $this->label . '</label>';
    $this->printInputTag();
    if ($this->validationResult && !$this->validationResult['success']) {
      echo '<div class="invalid-feedback">';
      echo implode(' ', $this->validationResult['errors']);
      echo '</div>';
    }
    echo '</div>';
  }

  abstract function printInputTag();
  
  function printInputAttributes() {
    echo ' class="' . implode(' ', $this->cssClasses) . '"';
    foreach ($this->attrs as $name => $value) {
      echo ' ' . $name . '="' . $value . '"';
    }
  }
    
  function getId() {
    return sanitize_title($this->label);
  }
  
  function getValue() {
    return $this->value;
  }
  
  function setTag($tag) {
    $this->tag = $tag;
    return $this;
  }
  
  function addAttribute($name, $value) {
    $this->attrs[$name] = $value;
    return $this;
  }
  
  function addValidator($validator) {
    $this->validators[] = $validator;
    return $this;
  }
  
  function required() {
    $this->addValidator(function($value) {
      if (!$value) {
        return 'Feld darf nicht leer sein';
      }
      return true;
    });
    return $this;
  }
}

class Input extends BaseInput {
  function __construct($label) {
    parent::__construct($label);
  }
  
  function processSubmission($data) {
    parent::processSubmission($data);
    $this->addAttribute('value', $this->value);
  }

  function printInputTag() {
    echo '<input ';
    $this->printInputAttributes();
    echo '>';
  }
}

class Textarea extends BaseInput {
  function __construct($label) {
    parent::__construct($label);
  }
  
  function printInputTag() {
    echo '<textarea ';
    $this->printInputAttributes();
    echo '>';
    echo $this->value;
    echo '</textarea>';
  }
}

class Checkbox extends BaseInput {
  function __construct($label) {
    parent::__construct($label);
    $this->cssClasses = array('form-check-input');
    $this->addAttribute('type', 'checkbox');
    $this->addAttribute('value', '1');
  }

  function processSubmission($data) {
    parent::processSubmission($data);
    if ($this->value) {
      $this->addAttribute('checked', 'checked');
    }
  }
  
  function printInput() {
    echo '<div class="form-group">';
    echo '<div class="form-check">';
    $this->printInputTag();
    echo '<label class="form-check-label" for="' . $this->getId() . '">' . $this->label . '</label>';
    if ($this->validationResult && !$this->validationResult['success']) {
      echo '<div class="invalid-feedback">';
      echo implode(' ', $this->validationResult['errors']);
      echo '</div>';
    }
    echo '</div>';
    echo '</div>';
  }
  
  function printInputTag() {
    echo '<input ';
    $this->printInputAttributes();
    echo '>';
  }
}


