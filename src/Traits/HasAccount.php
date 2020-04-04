<?php

namespace Jason\Account\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Jason\Account\Models\Account;

trait HasAccount
{

    /**
     * Notes: 初始化用户账户
     * @Author: <C.Jason>
     * @Date: 2020/1/14 4:10 下午
     */
    public static function bootHasAccount()
    {
        self::created(function ($model) {
            $model->account()->create();
        });
    }

    /**
     * Notes: 关联账户
     * @Author: <C.Jason>
     * @Date: 2020/1/14 4:11 下午
     * @return MorphOne
     */
    public function account(): MorphOne
    {
        return $this->morphOne(Account::class, 'accountable');
    }

}
