<?php
declare(strict_types=1);

function createMockResponse(array $data): object
{
    return new class($data) {
        public function __construct(private array $data) {}

        public function asArray(): array
        {
            return $this->data;
        }
    };
}

function createMockClient(array $responseData, ?callable $validator = null): object
{
    return new class($responseData, $validator) {
        private $validator;

        public function __construct(
            private readonly array $responseData,
            $validator
        ) {
            $this->validator = $validator;
        }

        public function search(array $params): object
        {
            if (is_callable($this->validator)) {
                call_user_func($this->validator, $params);
            }

            return new class($this->responseData) {
                public function __construct(private array $data) {}

                public function asArray(): array
                {
                    return $this->data;
                }
            };
        }
    };
}
