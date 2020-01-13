<?php

namespace Jason\Account\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountLog extends Model
{

    use SoftDeletes;

    protected $casts = [
        'source' => 'json',
    ];

    /**
     * Notes: 所属账户
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:30 下午
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Notes: 所属规则
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:30 下午
     * @return BelongsTo
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AccountRule::class);
    }

    /**
     * Notes: 冻结一条账户记录
     * @Author: <C.Jason>
     * @Date: 2019/12/1 10:48 上午
     * @return bool
     * @throws Exception
     */
    public function freeze()
    {
        if ($this->frozen == 0) {
            $this->account->decrement($this->type, $this->variable);
            $this->frozen  = 1;
            $this->balance = $this->account->{$this->type};
            $this->save();

            return true;
        } else {
            throw new Exception('账目已冻结');
        }
    }

    /**
     * Notes: 解冻一条记录
     * @Author: <C.Jason>
     * @Date: 2019/12/1 10:48 上午
     * @return bool
     * @throws Exception
     */
    public function thaw()
    {
        if ($this->frozen == 1) {
            $this->account->increment($this->type, $this->variable);
            $this->frozen  = 0;
            $this->balance = $this->account->{$this->type};
            $this->save();

            return true;
        } else {
            throw new Exception('账目已解冻');
        }
    }

    protected function getTypeTextAttribute()
    {
        $list = config('account.account_type');

        return $list[$this->type];
    }

}
