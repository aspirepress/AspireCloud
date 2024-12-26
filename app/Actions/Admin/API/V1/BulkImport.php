<?php

declare(strict_types=1);

namespace App\Actions\Admin\API\V1;

use App\Http\JsonResponses;
use Illuminate\Http\JsonResponse;

class BulkImport
{
    use JsonResponses;

    public function __invoke(): JsonResponse
    {
        return $this->success('brillant');
    }
}
