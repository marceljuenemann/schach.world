<?php

namespace Nsv\WebApp\Core;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse {

  public function __construct(mixed $data = null, int $status = 200) {
    parent::__construct($data, $status);
    $this->setEncodingOptions(JSON_PRETTY_PRINT);
  }

}
