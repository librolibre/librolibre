<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;

/**
 * A business rule was broken (BusinessRuleError in the API contract): the API answers 409
 * with { "message": "…", "code": "…" }. Throw it from a service, for example:
 *
 *     throw new BusinessRuleException('ADMIN_PROTECTED', 'No se puede suspender a un administrador.');
 *
 * The codes are the ones listed in BusinessRuleError.code in docs/api/openapi.yaml.
 * ShouldntReport: it is an expected answer, not a bug, so it is not written to the error log.
 */
class BusinessRuleException extends Exception implements ShouldntReport
{
    /**
     * @param  string  $errorCode  stable code for the front end (ADMIN_PROTECTED, INVALID_STATUS…)
     * @param  string  $message  text for the user, in Spanish
     */
    public function __construct(
        // Do not rename to $code: every PHP exception already has an integer $code property
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * Laravel calls this when the exception is not caught, and sends what it returns to the client.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => $this->errorCode,
        ], 409);
    }
}
