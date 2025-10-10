<?php

// Helper function to create mock response
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

// Helper function to create mock client
function createMockClient(array $responseData, ?callable $validator = null): object
{
    return new class($responseData, $validator) {
        private $validator;

        public function __construct(private readonly array $responseData, ?callable $validator) {
            $this->validator = $validator;
        }

        public function search(array $params) {
            if ($this->validator) {
                ($this->validator)($params);
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
