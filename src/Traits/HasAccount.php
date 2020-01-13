<?php

namespace Jason\Account\Traits;

use Jason\Account\Models\Account;

trait HasAccount
{

    /**
     * @var array
     */
    public static function bootHasAccount()
    {
        self::created(function ($model) {
            $model->account()->create();
        });
    }

    public function account()
    {
        return $this->morphOne(Account::class, 'accountable');
    }

}