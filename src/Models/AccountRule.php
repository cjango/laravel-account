<?php

namespace Jason\Account\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountRule extends Model
{

    use SoftDeletes;

    /**
     * Notes:
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:30 下午
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AccountLog::class);
    }

    /**
     * Notes: 获取触发次数的文字说明
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:30 下午
     * @return string
     */
    protected function getTriggerTextAttribute()
    {
        switch ($this->trigger <=> 0) {
            case -1:
                return '仅一次';
                break;
            case 0:
                return '不限制';
                break;
            case 1:
                return $this->trigger . ' 次/日';
                break;
        }
    }

}
