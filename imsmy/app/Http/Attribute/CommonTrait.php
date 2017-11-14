<?php
namespace App\Http\Attribute;

use App\Api\Controllers\SubscriptionController;

trait CommonTrait
{
    /**
     * 关注方面
     *
     * @return \
     */
    protected function subscription()
    {
        return app(SubscriptionController::class);
    }

}