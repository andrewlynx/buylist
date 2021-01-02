<?php

namespace App\Entity\JsonResponse;

use App\Constant\AppConstant;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonSuccess extends JsonResponse
{
    public function __construct($data)
    {
        parent::__construct([
            'status' => AppConstant::JSON_STATUS_SUCCESS,
            'data' => $data,
        ]);
    }
}