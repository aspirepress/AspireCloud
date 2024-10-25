<?php

namespace Database\Factories\Sync;

use App\Models\Sync\SyncPlugin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SyncPluginFactory extends Factory
{
    protected $model = SyncPlugin::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'id' => $this->faker->uuid(),
            'name' => $name,
            'slug' => Str::slug($name),
            'current_version' => $this->faker->semver(),
            'updated' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['open', 'processing', 'complete', 'error']),
            'pulled_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'metadata' => $this->generateMetadata(),
        ];
    }

    /**
     * State for newly created sync plugins
     */
    public function asNew(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'open',
            'pulled_at' => now(),
            'metadata' => array_merge(
                $this->generateMetadata(),
                ['sync_attempts' => 0]
            ),
        ]);
    }

    /**
     * State for plugins currently being processed
     */
    public function processing(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'processing',
            'updated' => now(),
            'metadata' => array_merge(
                $this->generateMetadata(),
                [
                    'last_sync_status' => 'in_progress',
                    'current_operation' => $this->faker->randomElement([
                        'downloading_files',
                        'parsing_metadata',
                        'updating_database',
                        'validating_content',
                    ]),
                ]
            ),
        ]);
    }

    /**
     * State for plugins with sync errors
     */
    public function withError(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'error',
            'metadata' => array_merge(
                $this->generateMetadata(),
                [
                    'last_sync_status' => 'failed',
                    'last_error' => $this->faker->sentence(),
                    'error_details' => [
                        'code' => $this->faker->randomElement(['DOWNLOAD_FAILED', 'INVALID_METADATA', 'PERMISSION_DENIED']),
                        'message' => $this->faker->sentence(),
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                        'stack_trace' => $this->faker->paragraphs(3, true),
                    ],
                ]
            ),
        ]);
    }

    /**
     * State for successfully completed syncs
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'complete',
            'metadata' => array_merge(
                $this->generateMetadata(),
                [
                    'last_sync_status' => 'success',
                    'completion_details' => [
                        'duration_seconds' => $this->faker->numberBetween(10, 300),
                        'files_processed' => $this->faker->numberBetween(10, 100),
                        'total_size_bytes' => $this->faker->numberBetween(100000, 5000000),
                        'completion_time' => now()->format('Y-m-d H:i:s'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * State for plugins with a high number of sync attempts
     */
    public function highSyncAttempts(): static
    {
        return $this->state(fn(array $attributes) => [
            'metadata' => array_merge(
                $this->generateMetadata(),
                [
                    'sync_attempts' => $this->faker->numberBetween(20, 50),
                    'sync_history' => array_merge(
                        $this->generateSyncHistory(),
                        [
                            [
                                'timestamp' => now()->format('Y-m-d H:i:s'),
                                'status' => 'failed',
                                'error_message' => 'Maximum sync attempts reached',
                            ],
                        ]
                    ),
                ]
            ),
        ]);
    }

    protected function generateMetadata(): array
    {
        return [
            'last_sync_status' => $this->faker->randomElement(['success', 'failed', 'partial']),
            'sync_attempts' => $this->faker->numberBetween(1, 10),
            'last_error' => $this->faker->optional(0.3)->sentence(),
            'file_count' => $this->faker->numberBetween(10, 100),
            'size_bytes' => $this->faker->numberBetween(100000, 5000000),
            'repository_info' => [
                'type' => $this->faker->randomElement(['svn', 'git']),
                'url' => $this->faker->url(),
                'branch' => $this->faker->optional()->word(),
            ],
            'wordpress_metadata' => [
                'requires' => $this->faker->semver(),
                'tested' => $this->faker->semver(),
                'requires_php' => '>=' . $this->faker->numberBetween(7, 8) . '.0',
            ],
            'sync_history' => $this->generateSyncHistory(),
            'dependencies' => $this->generateDependencies(),
        ];
    }

    protected function generateSyncHistory(): array
    {
        $history = [];
        $count = $this->faker->numberBetween(3, 8);

        for ($i = 0; $i < $count; $i++) {
            $history[] = [
                'timestamp' => $this->faker->dateTimeBetween('-3 months')->format('Y-m-d H:i:s'),
                'version' => $this->faker->semver(),
                'status' => $this->faker->randomElement(['success', 'failed', 'partial']),
                'duration_seconds' => $this->faker->numberBetween(10, 300),
                'files_processed' => $this->faker->numberBetween(5, 50),
                'error_message' => $this->faker->optional(0.2)->sentence(),
            ];
        }

        return $history;
    }

    protected function generateDependencies(): array
    {
        $dependencies = [];
        if ($this->faker->boolean(70)) {
            $count = $this->faker->numberBetween(1, 5);
            for ($i = 0; $i < $count; $i++) {
                $dependencies[] = [
                    'name' => $this->faker->words(2, true),
                    'version_constraint' => '>=' . $this->faker->semver(),
                    'type' => $this->faker->randomElement(['required', 'optional', 'recommended']),
                ];
            }
        }
        return $dependencies;
    }

}
