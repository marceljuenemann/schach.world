<?php
class UnknownIDException extends Exception
{
    // Die Exceptionmitteilung neu definieren, damit diese nicht optional ist
    public function __construct($id) {
        parent::__construct( "Die angegebene ID ist unbekannt" );
    }
}

class UnknownFieldException extends Exception
{
    // Die Exceptionmitteilung neu definieren, damit diese nicht optional ist
    public function __construct($name) {
        parent::__construct("Das Feld mit dem Namen $name ist unbekannt.");
    }
}

class WrongFormatException extends Exception
{
    // Die Exceptionmitteilung neu definieren, damit diese nicht optional ist
    public function __construct($message) {
        parent::__construct($message);
    }
}

?>
