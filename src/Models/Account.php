<?php

namespace Jason\Account\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class Account extends Model
{

    /**
     * Notes: 账户可用余额
     * @Author: <C.Jason>
     * @Date: 2019/11/28 2:26 下午
     * @return mixed
     */
    protected function getBalanceUseAttribute()
    {
        $ltZero = $this->logs()->where('frozen', 1)->where('variable', '<', 0)->sum('variable');

        return $this->balance + $ltZero;
    }

    /**
     * Notes: 冻结金额
     * @Author: <C.Jason>
     * @Date: 2019/11/28 2:27 下午
     * @return mixed
     */
    protected function getFrozenAttribute()
    {
        return $this->logs()->where('frozen', 1)->sum(DB::raw('abs(variable)'));
    }

    /**
     * Notes:
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:28 下午
     * @return MorphTo
     */
    public function accountable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Notes: 账户日志
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:28 下午
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AccountLog::class);
    }


    /**
     * Notes: 执行账户规则
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:24 下午
     * @param $rule string|int
     * @param float $variable
     * @param bool $frozen
     * @param array $source
     * @return bool
     * @throws Exception
     */
    public function rule($rule, float $variable = 0, bool $frozen = true, array $source = [])
    {
        if (is_numeric($rule)) {
            $rule = AccountRule::findOrFail($rule);
        } else {
            $rule = AccountRule::where('name', $rule)->firstOrFail();
        }

        if ($rule->trigger == 0) {
            // 不限制执行的
            return $this->accountExecute($rule, $variable, $frozen, $source);
        } elseif ($rule->trigger > $this->logs()->where('rule_id', $rule->id)->whereDate('created_at', Carbon::today())->count()) {
            // 每日执行 trigger 次
            return $this->accountExecute($rule, $variable, $frozen, $source);
        } elseif ($rule->trigger < 0 && !$this->logs()->where('rule_id', $rule->id)->first()) {
            // 终身只能执行一次
            return $this->accountExecute($rule, $variable, $frozen, $source);
        }

        throw new Exception('达到最大可执行次数');
    }

    /**
     * Notes: 增加账户余额
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:25 下午
     * @param $type
     * @param $variable
     * @return bool
     */
    public function increase($type, $variable)
    {
        DB::transaction(function () use ($type, $variable) {
            $this->increment($type, $variable);
            $log = [
                'rule_id'  => 0,
                'type'     => $type,
                'variable' => $variable,
                'frozen'   => 0,
                'balance'  => $this->{$type},
                'source'   => ['type' => 'increase'],
            ];
            $this->logs()->create($log);
        });

        return true;
    }

    /**
     * Notes: 扣除账户金额
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:25 下午
     * @param $type
     * @param $variable
     * @return bool
     * @throws Exception
     */
    public function decrease($type, $variable)
    {
        // 如果账户类型不可以为负数
        if (config('account.can_minus')[$type] === false && ($this->$type + $variable < 0)) {
            throw new Exception('【 ' . config('account.account_type')[$type] . ' 】 余额不足');
        }
        DB::transaction(function () use ($type, $variable) {
            $this->decrement($type, $variable);
            $log = [
                'rule_id'  => 0,
                'type'     => $type,
                'variable' => -$variable,
                'frozen'   => 0,
                'balance'  => $this->{$type},
                'source'   => ['type' => 'deduct'],
            ];
            $this->logs()->create($log);
        });

        return true;
    }

    /**
     * Notes: 执行账户规则
     * @Author: <C.Jason>
     * @Date: 2019/11/28 1:41 下午
     * @param AccountRule $rule
     * @param $variable
     * @param $frozen
     * @param $source
     * @return bool
     * @throws Exception
     */
    protected function accountExecute(AccountRule $rule, $variable, $frozen, $source)
    {
        if ($variable != 0) {
            $rule->variable = $variable;
        }

        // 账户余额不允许为负数的时候判断余额是否充足
        if ((config('account.can_minus')[$rule->type] == false) && ($rule->variable < 0) && ($rule->variable + $this->{$rule->type} < 0)) {
            throw new Exception('【 ' . config('account.account_type')[$rule->type] . ' 】 余额不足');
        }
        DB::transaction(function () use ($rule, $frozen, $source) {
            // 如果是扣款，立即执行，如果非冻结，也立即执行
            if (($rule->variable < 0 && config('account.deductions')) || $rule->deductions == 1 || $frozen === false) {
                $this->increment($rule->type, $rule->variable);
                $frozen = false;
            }
            $log = [
                'rule_id'  => $rule->id,
                'type'     => $rule->type,
                'variable' => $rule->variable,
                'frozen'   => $frozen,
                'balance'  => $this->{$rule->type},
                'source'   => $source ?: [],
            ];
            // 写入记录
            $this->logs()->create($log);
        });

        return true;
    }
}