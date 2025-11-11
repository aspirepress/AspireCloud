<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\WpOrg\Plugin;
use App\Services\Elastic\IndexService;

readonly class ElasticSearchObserver
{

    public function __construct(private IndexService $index) {}


    public function created(Plugin $plugin): void
    {
        $this->index->add($plugin);
    }

    public function updated(Plugin $plugin): void
    {
        $this->index->add($plugin);
    }

    public function deleted(Plugin $plugin): void
    {
        $this->index->remove($plugin);
    }
}
