<?php

namespace App\Values\WpOrg;

use App\Models\WpOrg\Author as AuthorModel;
use App\Values\DTO;
use Bag\Attributes\Transforms;

readonly class Author extends DTO
{
    public function __construct(
        public string $user_nicename,
        public string|null $profile,
        public string|null $avatar,
        public string|null $display_name,
        public string|null $author,
        public string|null $author_url,
    ) {}

    // I wish I didn't have to write this, but alas it serializes $model to json inside $user_nicename otherwise 🤦

    /** @return array<string, mixed> */
    #[Transforms(AuthorModel::class)]
    public static function fromModel(AuthorModel $model): array
    {
        return [
            'user_nicename' => $model->user_nicename,
            'profile' => $model->profile,
            'avatar' => $model->avatar,
            'display_name' => $model->display_name,
            'author' => $model->author,
            'author_url' => $model->author_url,
        ];
    }
}
