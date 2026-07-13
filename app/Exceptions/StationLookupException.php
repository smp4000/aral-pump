<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Fachliche Ausnahme für die externe Tankstellensuche.
 *
 * Der Benutzer erhält nur eine verständliche Meldung. Technische Details und
 * insbesondere der API-Schlüssel werden dadurch nicht versehentlich ausgegeben.
 */
class StationLookupException extends RuntimeException {}
